<?php
require __DIR__ . '/../vendor/autoload.php'; // Autoload Ratchet and dependencies

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

// Chat class to handle WebSocket connections and messages
class Chat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage; // Store connected clients
    }

    // Called when a new client connects
    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn); // Add the client to the list
        echo "New connection! ({$conn->resourceId})\n";
    }

    // Called when a client sends a message
    public function onMessage(ConnectionInterface $from, $msg) {
        // Decode the message
        $data = json_decode($msg, true);
    
        try {
            // Save the message to the database
            $pdo = new PDO('mysql:host=localhost;dbname=event_hub', 'root', '');
            $stmt = $pdo->prepare('INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)');
            $stmt->execute([
                ':sender_id' => $data['sender_id'],
                ':receiver_id' => $data['receiver_id'],
                ':message' => $data['message']
            ]);
        } catch (PDOException $e) {
            echo "Database error: " . $e->getMessage() . "\n";
            return; // Stop further execution if there's a database error
        }
    
        // Broadcast the message to the receiver
        foreach ($this->clients as $client) {
            if ($client->resourceId === $data['receiver_id']) {
                $client->send(json_encode($data));
            }
        }
    }

    // Called when a client disconnects
    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn); // Remove the client from the list
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    // Called when an error occurs
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Create the WebSocket server
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat()
        )
    ),
    8080 // Port to listen on
);

echo "WebSocket server started on port 8080\n";
$server->run();
?>