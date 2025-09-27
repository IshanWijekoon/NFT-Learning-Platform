<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web3 Wallet Dashboard - NFT Learning Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            color: #e1e5e9;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            text-align: center;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }

        .dashboard-header h1 {
            color: #f8f9fa;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }

        .wallet-connect-section {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }

        .wallet-status {
            display: none;
        }

        .wallet-status.connected {
            display: block;
        }

        .wallet-address {
            font-family: 'Courier New', monospace;
            background: rgba(102, 126, 234, 0.1);
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }

        .dashboard-content {
            display: none;
        }

        .dashboard-content.show {
            display: block;
        }

        .profile-section, .enrollments-section {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(102, 126, 234, 0.3);
        }

        .section-title {
            color: #f8f9fa;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #d1d5db;
            font-weight: 500;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid rgba(102, 126, 234, 0.3);
            border-radius: 8px;
            background: #2a2a2a;
            color: #e1e5e9;
            font-size: 1rem;
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .wallet-connect-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 auto;
        }

        .enrollments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .enrollment-card {
            background: #262626;
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s;
        }

        .enrollment-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .enrollment-card h3 {
            color: #f8f9fa;
            margin-bottom: 0.5rem;
        }

        .enrollment-card p {
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        .enrollment-date {
            color: #9ca3af;
            font-size: 0.9rem;
        }

        .loading {
            text-align: center;
            padding: 2rem;
            color: #9ca3af;
        }

        .error-message, .success-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .error-message {
            background: rgba(220, 38, 38, 0.1);
            border: 1px solid rgba(220, 38, 38, 0.3);
            color: #fca5a5;
        }

        .success-message {
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.3);
            color: #6ee7b7;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0 1rem;
            }
            
            .enrollments-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Learnity - Web3 Dashboard</div>
            <ul class="nav-links">
                <li><a href="course-browser.php"><i class="fas fa-book"></i> Browse Courses</a></li>
                <li><a href="course-browser.php"><i class="fas fa-home"></i> Home</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1><i class="fab fa-ethereum"></i> Web3 Wallet Dashboard</h1>
            <p>Manage your Web3 profile and view your blockchain-based course enrollments</p>
        </div>

        <div class="wallet-connect-section" id="walletConnectSection">
            <h2>Connect Your Web3 Wallet</h2>
            <p>Connect your MetaMask or other Web3 wallet to access your decentralized learning profile.</p>
            <button id="walletConnectBtn" class="wallet-connect-btn">
                <i class="fab fa-ethereum"></i>
                Connect Wallet
            </button>
        </div>

        <div class="wallet-status" id="walletStatus">
            <h3>Wallet Connected</h3>
            <div class="wallet-address" id="walletAddressDisplay"></div>
            <button id="walletDisconnectBtn" class="btn">Disconnect Wallet</button>
        </div>

        <div class="dashboard-content" id="dashboardContent">
            <div class="profile-section">
                <h2 class="section-title"><i class="fas fa-user"></i> Wallet Profile</h2>
                <div id="profileMessages"></div>
                <form id="profileForm">
                    <div class="form-group">
                        <label for="displayName">Display Name</label>
                        <input type="text" id="displayName" name="displayName" placeholder="Enter your display name">
                    </div>
                    <div class="form-group">
                        <label for="email">Email (Optional)</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email">
                    </div>
                    <div class="form-group">
                        <label for="bio">Bio (Optional)</label>
                        <textarea id="bio" name="bio" rows="4" placeholder="Tell us about yourself..."></textarea>
                    </div>
                    <button type="submit" class="btn" id="saveProfileBtn">
                        <i class="fas fa-save"></i> Save Profile
                    </button>
                </form>
            </div>

            <div class="enrollments-section">
                <h2 class="section-title"><i class="fas fa-graduation-cap"></i> Your Web3 Course Enrollments</h2>
                <div id="enrollmentsContainer">
                    <div class="loading" id="enrollmentsLoading">
                        <i class="fas fa-spinner fa-spin"></i> Loading enrollments...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Web3 Wallet Integration -->
    <script src="web3-wallet.js"></script>
    <script>
        class WalletDashboard {
            constructor() {
                this.currentWallet = null;
                this.profile = null;
                this.init();
            }

            async init() {
                // Wait for Web3 wallet to initialize
                setTimeout(() => {
                    this.setupEventListeners();
                    this.checkWalletConnection();
                }, 1000);
            }

            setupEventListeners() {
                const connectBtn = document.getElementById('walletConnectBtn');
                const disconnectBtn = document.getElementById('walletDisconnectBtn');
                const profileForm = document.getElementById('profileForm');

                if (connectBtn) {
                    connectBtn.addEventListener('click', () => this.connectWallet());
                }

                if (disconnectBtn) {
                    disconnectBtn.addEventListener('click', () => this.disconnectWallet());
                }

                if (profileForm) {
                    profileForm.addEventListener('submit', (e) => this.saveProfile(e));
                }
            }

            async connectWallet() {
                if (window.web3Wallet) {
                    const connected = await window.web3Wallet.connectWallet();
                    if (connected) {
                        this.currentWallet = window.web3Wallet.getCurrentAccount();
                        this.updateUI();
                        await this.loadProfile();
                        await this.loadEnrollments();
                    }
                }
            }

            async disconnectWallet() {
                if (window.web3Wallet) {
                    await window.web3Wallet.disconnectWallet();
                    this.currentWallet = null;
                    this.profile = null;
                    this.updateUI();
                }
            }

            checkWalletConnection() {
                if (window.web3Wallet && window.web3Wallet.isWalletConnected()) {
                    this.currentWallet = window.web3Wallet.getCurrentAccount();
                    this.updateUI();
                    this.loadProfile();
                    this.loadEnrollments();
                }
            }

            updateUI() {
                const connectSection = document.getElementById('walletConnectSection');
                const walletStatus = document.getElementById('walletStatus');
                const dashboardContent = document.getElementById('dashboardContent');
                const walletAddressDisplay = document.getElementById('walletAddressDisplay');

                if (this.currentWallet) {
                    connectSection.style.display = 'none';
                    walletStatus.classList.add('connected');
                    dashboardContent.classList.add('show');
                    if (walletAddressDisplay) {
                        walletAddressDisplay.textContent = this.currentWallet;
                    }
                } else {
                    connectSection.style.display = 'block';
                    walletStatus.classList.remove('connected');
                    dashboardContent.classList.remove('show');
                }
            }

            async loadProfile() {
                if (!this.currentWallet) return;

                try {
                    const response = await fetch('wallet_profile_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'get',
                            wallet_address: this.currentWallet
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        this.profile = result.profile;
                        this.populateProfileForm();
                        if (result.created) {
                            this.showMessage('Profile created for your wallet!', 'success');
                        }
                    }
                } catch (error) {
                    console.error('Error loading profile:', error);
                    this.showMessage('Failed to load profile', 'error');
                }
            }

            populateProfileForm() {
                if (!this.profile) return;

                const displayName = document.getElementById('displayName');
                const email = document.getElementById('email');
                const bio = document.getElementById('bio');

                if (displayName) displayName.value = this.profile.display_name || '';
                if (email) email.value = this.profile.email || '';
                if (bio) bio.value = this.profile.bio || '';
            }

            async saveProfile(e) {
                e.preventDefault();
                if (!this.currentWallet) return;

                const saveBtn = document.getElementById('saveProfileBtn');
                const formData = new FormData(e.target);

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

                try {
                    const response = await fetch('wallet_profile_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'update',
                            wallet_address: this.currentWallet,
                            display_name: formData.get('displayName'),
                            email: formData.get('email'),
                            bio: formData.get('bio')
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        this.showMessage('Profile updated successfully!', 'success');
                    } else {
                        this.showMessage('Failed to update profile', 'error');
                    }
                } catch (error) {
                    console.error('Error saving profile:', error);
                    this.showMessage('Failed to save profile', 'error');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Profile';
                }
            }

            async loadEnrollments() {
                if (!this.currentWallet) return;

                const container = document.getElementById('enrollmentsContainer');
                const loading = document.getElementById('enrollmentsLoading');

                try {
                    const response = await fetch('wallet_profile_api.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'get_enrollments',
                            wallet_address: this.currentWallet
                        })
                    });

                    const result = await response.json();
                    if (result.success) {
                        this.renderEnrollments(result.enrollments);
                    }
                } catch (error) {
                    console.error('Error loading enrollments:', error);
                    container.innerHTML = '<div class="error-message">Failed to load enrollments</div>';
                } finally {
                    if (loading) loading.style.display = 'none';
                }
            }

            renderEnrollments(enrollments) {
                const container = document.getElementById('enrollmentsContainer');
                
                if (enrollments.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #9ca3af;">No Web3 enrollments found. <a href="course-browser.php" style="color: #667eea;">Browse courses</a> to get started!</p>';
                    return;
                }

                const grid = document.createElement('div');
                grid.className = 'enrollments-grid';

                enrollments.forEach(enrollment => {
                    const card = document.createElement('div');
                    card.className = 'enrollment-card';
                    
                    card.innerHTML = `
                        <h3>${this.escapeHtml(enrollment.course_name)}</h3>
                        <p>${this.escapeHtml(enrollment.description?.substring(0, 100) + '...' || 'No description available')}</p>
                        <div class="enrollment-date">
                            <i class="fas fa-calendar"></i> Enrolled: ${new Date(enrollment.enrollment_date).toLocaleDateString()}
                        </div>
                        <div style="margin-top: 1rem;">
                            <span style="background: rgba(102, 126, 234, 0.2); padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.8rem;">
                                <i class="fab fa-ethereum"></i> Web3 Enrollment
                            </span>
                        </div>
                    `;

                    grid.appendChild(card);
                });

                container.innerHTML = '';
                container.appendChild(grid);
            }

            showMessage(message, type) {
                const messagesContainer = document.getElementById('profileMessages');
                const messageEl = document.createElement('div');
                messageEl.className = `${type}-message`;
                messageEl.textContent = message;

                messagesContainer.innerHTML = '';
                messagesContainer.appendChild(messageEl);

                setTimeout(() => {
                    messageEl.remove();
                }, 5000);
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text || '';
                return div.innerHTML;
            }
        }

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', () => {
            new WalletDashboard();
        });
    </script>
</body>
</html>