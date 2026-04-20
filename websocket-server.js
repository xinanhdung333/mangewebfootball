import { createServer } from 'http';
import { WebSocketServer } from 'ws';

const port = process.env.WS_PORT ? Number(process.env.WS_PORT) : 6001;
const subscriptions = new Map();

const httpServer = createServer((req, res) => {
    if (req.method === 'POST' && req.url === '/broadcast') {
        let body = '';

        req.on('data', chunk => {
            body += chunk;
        });

        req.on('end', () => {
            try {
                const payload = JSON.parse(body);
                const conversationId = payload.conversation_id;
                const message = payload.message;

                if (!conversationId || !message) {
                    res.writeHead(400, { 'Content-Type': 'application/json' });
                    return res.end(JSON.stringify({ error: 'Missing conversation_id or message' }));
                }

                const clients = subscriptions.get(conversationId) ?? new Set();
                const data = JSON.stringify({ type: 'message', conversation_id: conversationId, message });

                for (const client of clients) {
                    if (client.readyState === 1) {
                        client.send(data);
                    }
                }

                res.writeHead(200, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ status: 'ok' }));
            } catch (error) {
                res.writeHead(400, { 'Content-Type': 'application/json' });
                res.end(JSON.stringify({ error: error.message }));
            }
        });

        return;
    }

    res.writeHead(404);
    res.end();
});

const wss = new WebSocketServer({ server: httpServer });

wss.on('connection', (socket) => {
    socket.subscriptions = new Set();

    socket.on('message', (raw) => {
        try {
            const data = JSON.parse(raw.toString());
            if (data.type === 'subscribe' && data.conversation_id) {
                const conversationId = data.conversation_id;
                socket.subscriptions.add(conversationId);

                if (!subscriptions.has(conversationId)) {
                    subscriptions.set(conversationId, new Set());
                }

                subscriptions.get(conversationId).add(socket);
            }
        } catch {
            // ignore malformed messages
        }
    });

    socket.on('close', () => {
        for (const conversationId of socket.subscriptions) {
            const clients = subscriptions.get(conversationId);
            if (!clients) continue;
            clients.delete(socket);
            if (clients.size === 0) {
                subscriptions.delete(conversationId);
            }
        }
    });
});

httpServer.listen(port, () => {
    console.log(`WebSocket server is listening on ws://127.0.0.1:${port}`);
});
