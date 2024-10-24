require('dotenv').config();
const express = require('express');
const jwt = require('jsonwebtoken');
const hedera = require('@hashgraph/sdk');
const bcrypt = require('bcrypt');
const sqlite3 = require('sqlite3').verbose();

const { Client, AccountId, PrivateKey, TokenType, TokenCreateTransaction, AccountBalanceQuery, TokenInfoQuery } = require('@hashgraph/sdk');

const db = new sqlite3.Database('pulpoline.db');

const app = express();
const port = process.env.PORT || 3000;

const accountId = AccountId.fromString(process.env.OPERATOR_ID);
const accountKey = PrivateKey.fromStringECDSA(process.env.OPERATOR_KEY);
const client = Client.forTestnet().setOperator(accountId,accountKey);

app.use(express.json());

db.run(`
    CREATE TABLE IF NOT EXISTS users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE,
      email TEXT UNIQUE,
      password TEXT,
      hedera_token_id TEXT
    )
`);

function authenticateToken(req, res, next) {
    const token = req.headers['authorization'] && req.headers['authorization'].split(' ')[1];

    if (!token) return res.sendStatus(401);

    jwt.verify(token, process.env.JWT_SECRET, (err, user) => {
        if (err) return res.sendStatus(403);
        req.user = user; 
        next();
    });
}

app.post('/login', async (req, res) => {
    const { username, password } = req.body;
  
    db.get('SELECT * FROM users WHERE username = ?', [username], async (err, row) => {
      if (err) {
        console.error(err);   
  
        res.status(500).json({   
           error: 'Error al buscar al usuario' });
        return;
      }
  
      if (!row) {
        return res.status(401).json({ error: 'Credenciales inválidas' });
      }
  
      const match = await bcrypt.compare(password, row.password);
      if (!match) {
        return res.status(401).json({ error: 'Credenciales inválidas' });
      }
  
      const token = jwt.sign({ userId: row.id, tokenId: row.hedera_token_id }, process.env.JWT_SECRET, { expiresIn: '1h' });
      res.json({ token });
    });
});

app.post('/register', async (req, res) => {
  const { username, email, password } = req.body;
  const hashedPassword = await bcrypt.hash(password, 10);

      let tokenCreateTx = await new TokenCreateTransaction()
      .setTokenType(TokenType.FungibleCommon)
      .setTokenName(username)
      .setTokenSymbol(username.substring(0, 4).toUpperCase())
      .setDecimals(2)
      .setInitialSupply(100)
      .setTreasuryAccountId(accountId)
      .setAdminKey(accountKey)
      .setFreezeDefault(false)
      .freezeWith(client);
  
      const tokenCreateTxSigned = await tokenCreateTx.sign(accountKey);
      const tokenCreateTxSubmitted = await tokenCreateTxSigned.execute(client);
      const tokenCreateTxReceipt = await tokenCreateTxSubmitted.getReceipt(client);
      const tokenId = tokenCreateTxReceipt.tokenId.toString(); 

    try {
      db.run('INSERT INTO users (username, email, password) VALUES (?, ?, ?)',
        [username, email, hashedPassword],
        (err) => {
          if (err) {
            console.error(err);
            res.status(500).json({ error: 'Error al registrar el usuario' });
            return;
          }
  
          db.get('SELECT last_insert_rowid() as id', (err, row) => {
            if (err) {
              console.error(err);
              res.status(500).json({ error: 'Error al obtener el ID del usuario' });
              return;
            }
  
            db.run('UPDATE users SET hedera_token_id = ? WHERE id = ?',
              [tokenId, row.id],
              (err) => {
                if (err) {
                  console.error(err);
                  res.status(500).json({ error: 'Error al actualizar el usuario' });
                  return;
                }
  
                const token = jwt.sign({ userId: row.id, tokenId }, process.env.JWT_SECRET, { expiresIn: '1h' });
  
                res.json({ token });
              }
            );
          });
        }
      );
    } catch (err) {
      console.error(err);
      res.status(500).json({ error: 'Error al registrar el usuario' });
    }
  });

app.post('/create-token-hedera', authenticateToken, async (req, res) => {
    const { name, symbol, initialSupply } = req.body;

    let tokenCreateTx = await new TokenCreateTransaction()
    .setTokenType(TokenType.FungibleCommon)
    .setTokenName(name)
    .setTokenSymbol(symbol)
    .setDecimals(2)
    .setInitialSupply(initialSupply)
    .setTreasuryAccountId(accountId)
    .setAdminKey(accountKey)
    .setFreezeDefault(false)
    .freezeWith(client);

    const tokenCreateTxSigned = await tokenCreateTx.sign(accountKey);
    const tokenCreateTxSubmitted = await tokenCreateTxSigned.execute(client);
    const tokenCreateTxReceipt = await tokenCreateTxSubmitted.getReceipt(client);
    const tokenId = tokenCreateTxReceipt.tokenId; 

    res.json({ tokenId });
});

app.get('/list-tokens', authenticateToken, async (req, res) => {
    try {
        //const tokenQuery = new TokenQuery().setAccountId(process.env.OPERATOR_ID);

        /*const tokenQuery = new TokenInfoQuery().setAccountId(process.env.OPERATOR_ID);
        const tokens = await tokenQuery.execute(client);
        
        res.status(200).json(tokens);
        */
        const accountBalanceQuery = new AccountBalanceQuery().setAccountId(process.env.OPERATOR_ID);

        const accountBalance = await accountBalanceQuery.execute(client);
        let tokens = [];
        for (const [tokenId, balance] of Object.entries(accountBalance.tokens)) {
            tokens.push({tokenId: `${tokenId}`, balance: `${balance}`});
        }

        res.status(200).json(tokens);
    } catch (error) {
        console.error('Error al obtener tokens:', error);
        res.status(500).json({ error: 'Error al obtener tokens' });
    }
});

app.post('/logout', authenticateToken, (req, res) => {
    res.json({ message: 'Sesión cerrada exitosamente' });
});

app.listen(port, () => {
    console.log(`Server listening on port ${port}`);
});