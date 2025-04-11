<?php
// Start or resume existing session to maintain user state
session_start();

// Include database connection file for user data
require 'userdb.php';

// Get the logged-in user's ID from session (assumes user_id is set during login)
$loggedInUserId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Basic meta tags for character set and responsive viewport -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Page title -->
    <title>Chats | EventHub</title>
    
    <!-- External CSS libraries -->
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome for additional icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS files -->
    <link rel="stylesheet" href="/css/navbar.css">
    <link rel="stylesheet" href="/css/chats.css">
    <link rel="stylesheet" href="/css/footer.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <!-- Brand/logo link to homepage -->
            <a class="navbar-brand" href="../php/welcome.php">EventHub</a>
            
            <!-- Mobile menu toggle button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Home/Feed link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/welcome.php">
                            <i class="bi bi-house-door me-1"></i>Feed
                        </a>
                    </li>
                    
                    <!-- Events link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/calendar.php">
                            <i class="bi bi-calendar-event me-1"></i>Events
                        </a>
                    </li>
                    
                    <!-- Chats link (active page) -->
                    <li class="nav-item">
                        <a class="nav-link active" href="../php/chats.php">
                            <i class="bi bi-chat-left-text me-1"></i>Chats
                        </a>
                    </li>
                    
                    <!-- Recommendations link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/recommendation.php">
                            <i class="bi bi-stars me-1"></i>Recommended
                        </a>
                    </li>
                    
                    <!-- Profile link -->
                    <li class="nav-item">
                        <a class="nav-link" href="../php/profile.php">
                            <i class="bi bi-person me-1"></i>Profile
                        </a>
                    </li>
                    
                    <!-- Logout button -->
                    <li class="nav-item ms-2">
                        <a href="../php/logout.php" class="btn btn-danger">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="container mt-5 mb-5 pt-4">
        <!-- Chat interface container -->
        <div class="chat-row mt-5">
            <!-- Users List Section -->
            <div class="users-list-container pt-3">
                <!-- Users list header -->
                <div class="users-list-header">
                    <h4 class="mb-0 fw-bold lead"><i class="bi bi-chat-square-text me-2" style="color: blue; margin-left: 1rem;"></i> My Chats</h4>
                </div>
                
                <!-- Search bar for filtering users -->
                <div class="search-bar" style="padding: 0rem 1rem 0rem 1rem;">
                    <input type="text" id="search-input" class="form-control" placeholder="Search users...">
                </div>
                
                <!-- Container for the list of users -->
                <div id="users-list" class="users-list"></div>
                
                <!-- Back button (desktop version) -->
                <button id="back-button" class="btn btn-primary desktop" onclick="showUsersList()">
                    <i class="fas fa-arrow-left me-1"></i> Back to Chats
                </button>
            </div>
            
            <!-- Chat Window Section -->
            <div class="chat-window-container">
                <!-- Main chat window (hidden by default) -->
                <div id="chat-window" style="display: none;">
                    <!-- Chat header with back button and user info -->
                    <div class="chat-header">
                        <i class="bi bi-arrow-left chat-header-back" onclick="showUsersList()"></i>
                        <div class="chat-header-avatar">
                            <img id="chat-header-avatar" src="" alt="">
                        </div>
                        <div id="chat-header"></div>
                    </div>
                    
                    <!-- Container for chat messages -->
                    <div id="chat-messages" class="chat-messages">
                        <!-- Empty state when no chat is selected -->
                        <div class="empty-state">
                            <i class="bi bi-chat-square-text"></i>
                            <h5>Select a chat to start messaging</h5>
                            <p>Choose a conversation from your contacts list</p>
                        </div>
                    </div>
                    
                    <!-- Message input area -->
                    <div class="chat-input">
                        <textarea id="message-input" placeholder="Type your message..." rows="1"></textarea>
                        <button class="btn btn-primary" onclick="sendMessage()">
                            <i class="fas fa-paper-plane me-1"></i> Send
                        </button>
                    </div>
                </div>
                
                <!-- Empty state when no chat is selected -->
                <div id="no-chat-selected" class="empty-state" style="display: flex;">
                    <i class="bi bi-chat-square-text" style="font-size: 3rem;"></i>
                    <h4>No chat selected</h4>
                    <p>Select a conversation from the list to start chatting</p>
                </div>
            </div>
        </div>
        
        <!-- Mobile back button (hidden on desktop) -->
        <button id="back-button" class="btn btn-primary mobile" onclick="showUsersList()">
            <i class="fas fa-arrow-left me-1"></i> Back to Chats
        </button>
    </main>

    <!-- Footer Section -->
    <footer class="footer bg-dark mt-auto">
        <div class="container">
            <div class="row">
                <div class="col text-center">
                    <!-- Copyright notice -->
                    <p>&copy; 2025 Event Hub. All rights reserved.</p>
                    
                    <!-- Footer links -->
                    <ul class="footer-links list-inline">
                        <li class="list-inline-item"><a href="../php/welcome.php">Feed</a></li>
                        <li class="list-inline-item"><a href="../php/calendar.php">Events</a></li>
                        <li class="list-inline-item"><a href="../php/chats.php">Chats</a></li>
                        <li class="list-inline-item"><a href="../php/recommendation.php">Recommended</a></li>
                        <li class="list-inline-item"><a href="../php/profile.php">Profile</a></li>
                        <li class="list-inline-item"><a href="#">Privacy Policy</a></li>
                        <li class="list-inline-item"><a href="#">Terms of Service</a></li>
                        <li class="list-inline-item"><a href="#">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript Libraries -->
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chat Application JavaScript -->
    <script>
        // Global variables for chat functionality
        let currentReceiverId = null;  // ID of the user we're chatting with
        let socket = null;             // WebSocket connection
        let currentReceiverAvatar = ''; // Avatar of the current chat partner

        // Establish WebSocket connection
        function connectWebSocket() {
            // Create new WebSocket connection to local server
            socket = new WebSocket('ws://localhost:8080');

            // Connection established handler
            socket.onopen = () => {
                console.log('Connected to the WebSocket server');
            };

            // Message received handler
            socket.onmessage = (event) => {
                const message = JSON.parse(event.data);
                console.log('Received message:', message);
                handleIncomingMessage(message);
            };

            // Connection closed handler (with auto-reconnect)
            socket.onclose = () => {
                console.log('Disconnected from the WebSocket server');
                // Attempt to reconnect after 5 seconds
                setTimeout(connectWebSocket, 5000);
            };

            // Error handler
            socket.onerror = (error) => {
                console.error('WebSocket error:', error);
            };
        }

        // Process incoming messages
        function handleIncomingMessage(message) {
            // Check if message is intended for current user
            if (message.receiver_id === <?php echo $loggedInUserId; ?>) {
                const chatWindow = document.getElementById('chat-messages');
                
                // Remove empty state if it exists
                const emptyState = chatWindow.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.remove();
                }
                
                // Create and append new message element
                const messageElement = document.createElement('div');
                messageElement.classList.add('message', 'received');
                messageElement.innerHTML = `
                    <div class="message-sender">${message.sender_name}</div>
                    <div class="message-content">${message.message}</div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                `;
                chatWindow.appendChild(messageElement);
                // Scroll to bottom of chat
                chatWindow.scrollTop = chatWindow.scrollHeight;
            }
        }

        // Load and display all available users
        function fetchUsers() {
            fetch('../php/fetch_users.php')
                .then(response => response.json())
                .then(users => {
                    const usersList = document.getElementById('users-list');
                    usersList.innerHTML = '';
                    
                    // Show empty state if no users found
                    if (users.length === 0) {
                        usersList.innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-people"></i>
                                <p>No contacts available</p>
                            </div>
                        `;
                        return;
                    }
                    
                    // Create user list items
                    users.forEach(user => {
                        const userElement = document.createElement('div');
                        userElement.classList.add('user-item');
                        userElement.innerHTML = `
                            <div class="user-avatar">
                                <img src="${user.profile_pic}" alt="${user.username}">
                            </div>
                            <div class="user-info">
                                <div class="user-name">${user.username}</div>
                                <div class="user-type">${user.user_type === 'student' ? 'Student' : 'Host'}</div>
                            </div>
                        `;
                        
                        // Click handler for user selection
                        userElement.addEventListener('click', () => {
                            // Update active states
                            document.querySelectorAll('.user-item').forEach(item => {
                                item.classList.remove('active');
                            });
                            userElement.classList.add('active');
                            
                            // Set current chat partner
                            currentReceiverId = user.id;
                            currentReceiverAvatar = user.profile_pic;
                            
                            // Mobile view adjustments
                            if (window.innerWidth <= 992) {
                                document.querySelector('.users-list-container').classList.add('hidden');
                                document.querySelector('.chat-window-container').classList.add('visible');
                                document.getElementById('back-button').classList.add('mobile');
                            }
                            
                            // Update UI for chat window
                            document.getElementById('chat-window').style.display = 'flex';
                            document.getElementById('no-chat-selected').style.display = 'none';
                            document.getElementById('back-button').style.display = 'block';
                            document.getElementById('chat-header').textContent = user.username;
                            document.getElementById('chat-header-avatar').src = user.profile_pic;
                            
                            // Load messages for this conversation
                            fetchMessages();
                        });
                        usersList.appendChild(userElement);
                    });
                })
                .catch(error => console.error('Error fetching users:', error));
        }

        // Load messages for the selected conversation
        function fetchMessages() {
            if (!currentReceiverId) return;

            fetch(`../php/fetch_messages.php?sender_id=<?php echo $loggedInUserId; ?>&receiver_id=${currentReceiverId}`)
                .then(response => response.json())
                .then(messages => {
                    const chatWindow = document.getElementById('chat-messages');
                    chatWindow.innerHTML = '';
                    
                    // Show empty state if no messages
                    if (messages.length === 0) {
                        chatWindow.innerHTML = `
                            <div class="empty-state">
                                <i class="bi bi-chat-left"></i>
                                <p>No messages yet</p>
                                <small>Start the conversation!</small>
                            </div>
                        `;
                        return;
                    }
                    
                    // Create message elements
                    messages.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.classList.add('message', 
                            message.sender_id === <?php echo $loggedInUserId; ?> ? 'sent' : 'received');
                        messageElement.innerHTML = `
                            ${message.sender_id !== <?php echo $loggedInUserId; ?> ? 
                                `<div class="message-sender">${message.sender_name}</div>` : ''}
                            <div class="message-content">${message.message}</div>
                            <div class="message-time">${new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                        `;
                        chatWindow.appendChild(messageElement);
                    });
                    // Scroll to bottom of chat
                    chatWindow.scrollTop = chatWindow.scrollHeight;
                })
                .catch(error => console.error('Error fetching messages:', error));
        }

        // Send a new message
        function sendMessage() {
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();
            
            if (message && currentReceiverId) {
                const data = {
                    sender_id: <?php echo $loggedInUserId; ?>,
                    receiver_id: currentReceiverId,
                    message: message
                };

                // Send message via WebSocket
                socket.send(JSON.stringify(data));

                // Display the message immediately in the chat
                const chatWindow = document.getElementById('chat-messages');
                
                // Remove empty state if it exists
                const emptyState = chatWindow.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.remove();
                }
                
                // Create and append sent message element
                const messageElement = document.createElement('div');
                messageElement.classList.add('message', 'sent');
                messageElement.innerHTML = `
                    <div class="message-content">${message}</div>
                    <div class="message-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
                `;
                chatWindow.appendChild(messageElement);
                // Scroll to bottom of chat
                chatWindow.scrollTop = chatWindow.scrollHeight;

                // Clear input and reset height
                messageInput.value = '';
                messageInput.style.height = 'auto';
            }
        }

        // Show the users list (primarily for mobile view)
        function showUsersList() {
            // Mobile view adjustments
            if (window.innerWidth <= 992) {
                document.querySelector('.users-list-container').classList.remove('hidden');
                document.querySelector('.chat-window-container').classList.remove('visible');
                document.getElementById('back-button').classList.remove('mobile');
            }
            
            // Reset chat window state
            document.getElementById('chat-window').style.display = 'none';
            document.getElementById('no-chat-selected').style.display = 'flex';
            document.getElementById('back-button').style.display = 'none';
            
            // Clear active states from user items
            document.querySelectorAll('.user-item').forEach(item => {
                item.classList.remove('active');
            });
        }

        // Filter users based on search input
        function filterUsers(searchTerm) {
            const userItems = document.querySelectorAll('.user-item');
            userItems.forEach(user => {
                const username = user.querySelector('.user-name').textContent.toLowerCase();
                const userType = user.querySelector('.user-type').textContent.toLowerCase();
                if (username.includes(searchTerm) || userType.includes(searchTerm)) {
                    user.style.display = 'flex';
                } else {
                    user.style.display = 'none';
                }
            });
        }

        // Initialize the chat application when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Load users and connect to WebSocket
            fetchUsers();
            connectWebSocket();

            // Search functionality
            document.getElementById('search-input').addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                filterUsers(searchTerm);
            });

            // Auto-expanding textarea for message input
            const textarea = document.getElementById('message-input');
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });

            // Send message on Enter key (but allow Shift+Enter for new lines)
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });

            // Handle window resize for responsive layout
            window.addEventListener('resize', function() {
                if (window.innerWidth > 992) {
                    document.querySelector('.users-list-container').classList.remove('hidden');
                    document.querySelector('.chat-window-container').classList.remove('visible');
                    document.getElementById('back-button').classList.remove('mobile');
                }
            });
        });
    </script>
</body>
</html>