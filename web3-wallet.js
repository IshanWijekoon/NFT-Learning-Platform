/**
 * Web3 Wallet Integration for NFT Learning Platform
 * Supports MetaMask and WalletConnect
 */

class Web3WalletManager {
  constructor() {
    this.isConnected = false;
    this.currentAccount = null;
    this.provider = null;
    this.web3 = null;
    this.chainId = null;

    // Bind methods
    this.connectWallet = this.connectWallet.bind(this);
    this.disconnectWallet = this.disconnectWallet.bind(this);
    this.handleAccountsChanged = this.handleAccountsChanged.bind(this);
    this.handleChainChanged = this.handleChainChanged.bind(this);

    this.init();
  }

  async init() {
    // Check if already connected
    if (typeof window.ethereum !== "undefined") {
      this.provider = window.ethereum;

      // Set up event listeners
      this.provider.on("accountsChanged", this.handleAccountsChanged);
      this.provider.on("chainChanged", this.handleChainChanged);

      // Check if already connected
      try {
        const accounts = await this.provider.request({
          method: "eth_accounts",
        });
        if (accounts.length > 0) {
          this.currentAccount = accounts[0];
          this.isConnected = true;
          this.updateUI();
        }
      } catch (error) {
        console.error("Error checking wallet connection:", error);
      }
    }
  }

  async connectWallet() {
    if (typeof window.ethereum === "undefined") {
      this.showError(
        "MetaMask is not installed. Please install MetaMask to use wallet features."
      );
      return false;
    }

    try {
      // Request account access
      const accounts = await this.provider.request({
        method: "eth_requestAccounts",
      });

      if (accounts.length > 0) {
        this.currentAccount = accounts[0];
        this.isConnected = true;

        // Get chain ID
        this.chainId = await this.provider.request({ method: "eth_chainId" });

        // Save wallet connection to session/local storage
        this.saveWalletSession();

        // Update UI
        this.updateUI();

        // Show success message
        this.showSuccess(
          `Wallet connected: ${this.formatAddress(this.currentAccount)}`
        );

        return true;
      }
    } catch (error) {
      console.error("Error connecting wallet:", error);
      if (error.code === 4001) {
        this.showError("Wallet connection rejected by user.");
      } else {
        this.showError("Failed to connect wallet. Please try again.");
      }
      return false;
    }
  }

  async disconnectWallet() {
    this.isConnected = false;
    this.currentAccount = null;
    this.provider = null;
    this.chainId = null;

    // Clear saved session
    this.clearWalletSession();

    // Update UI
    this.updateUI();

    this.showSuccess("Wallet disconnected successfully.");
  }

  handleAccountsChanged(accounts) {
    if (accounts.length === 0) {
      // User disconnected wallet
      this.disconnectWallet();
    } else if (accounts[0] !== this.currentAccount) {
      // User switched accounts
      this.currentAccount = accounts[0];
      this.updateUI();
      this.showSuccess(
        `Switched to account: ${this.formatAddress(this.currentAccount)}`
      );
    }
  }

  handleChainChanged(chainId) {
    this.chainId = chainId;
    // Reload the page to reset any state that might be affected by the chain change
    window.location.reload();
  }

  saveWalletSession() {
    const walletData = {
      isConnected: this.isConnected,
      currentAccount: this.currentAccount,
      chainId: this.chainId,
      timestamp: Date.now(),
    };
    localStorage.setItem("web3_wallet_session", JSON.stringify(walletData));
  }

  clearWalletSession() {
    localStorage.removeItem("web3_wallet_session");
  }

  getWalletSession() {
    const saved = localStorage.getItem("web3_wallet_session");
    if (saved) {
      try {
        return JSON.parse(saved);
      } catch (error) {
        console.error("Error parsing saved wallet session:", error);
        this.clearWalletSession();
      }
    }
    return null;
  }

  updateUI() {
    const connectBtn = document.getElementById("walletConnectBtn");
    const disconnectBtn = document.getElementById("walletDisconnectBtn");
    const walletInfo = document.getElementById("walletInfo");
    const walletAddress = document.getElementById("walletAddress");

    if (!connectBtn) return;

    if (this.isConnected && this.currentAccount) {
      connectBtn.style.display = "none";
      if (disconnectBtn) disconnectBtn.style.display = "inline-block";
      if (walletInfo) walletInfo.style.display = "block";
      if (walletAddress)
        walletAddress.textContent = this.formatAddress(this.currentAccount);

      // Update enrollment buttons to show Web3 option
      this.updateEnrollmentButtons();
    } else {
      connectBtn.style.display = "inline-block";
      if (disconnectBtn) disconnectBtn.style.display = "none";
      if (walletInfo) walletInfo.style.display = "none";

      // Update enrollment buttons to hide Web3 option
      this.updateEnrollmentButtons();
    }
  }

  updateEnrollmentButtons() {
    const enrollButtons = document.querySelectorAll("[data-course-id]");
    enrollButtons.forEach((button) => {
      const courseId = button.getAttribute("data-course-id");
      if (this.isConnected) {
        // Add Web3 enrollment option if not already added
        if (!button.parentNode.querySelector(".web3-enroll-btn")) {
          this.addWeb3EnrollButton(button, courseId);
        }
      } else {
        // Remove Web3 enrollment option
        const web3Btn = button.parentNode.querySelector(".web3-enroll-btn");
        if (web3Btn) {
          web3Btn.remove();
        }
      }
    });
  }

  addWeb3EnrollButton(originalButton, courseId) {
    const web3Button = document.createElement("button");
    web3Button.className = "web3-enroll-btn btn-web3";
    web3Button.setAttribute("data-course-id", courseId);
    web3Button.innerHTML = '<i class="fab fa-ethereum"></i> Enroll with Web3';
    web3Button.onclick = () => this.enrollWithWeb3(courseId);

    // Insert after the original button
    originalButton.parentNode.insertBefore(
      web3Button,
      originalButton.nextSibling
    );
  }

  async enrollWithWeb3(courseId) {
    if (!this.isConnected) {
      this.showError("Please connect your wallet first.");
      return;
    }

    const button = document.querySelector(
      `[data-course-id="${courseId}"].web3-enroll-btn`
    );
    if (button) {
      button.disabled = true;
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }

    try {
      // Create enrollment transaction data
      const enrollmentData = {
        course_id: courseId,
        wallet_address: this.currentAccount,
        chain_id: this.chainId,
        enrollment_type: "web3",
        timestamp: Math.floor(Date.now() / 1000),
      };

      // Sign the enrollment data
      const signature = await this.signEnrollmentData(enrollmentData);

      if (signature) {
        // Send to backend for processing
        const response = await fetch("enroll_course_web3.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            ...enrollmentData,
            signature: signature,
          }),
        });

        const result = await response.json();

        if (result.success) {
          this.showSuccess(
            result.message || "Successfully enrolled with Web3 wallet!"
          );

          // Update button state
          if (button) {
            button.innerHTML = '<i class="fas fa-check"></i> Enrolled (Web3)';
            button.classList.add("btn-enrolled");
            button.disabled = true;
          }

          // Refresh the courses to update enrollment count
          if (window.courseBrowser) {
            window.courseBrowser.loadCourses();
          }
        } else {
          throw new Error(result.message || "Enrollment failed");
        }
      }
    } catch (error) {
      console.error("Web3 enrollment error:", error);
      this.showError(
        error.message || "Failed to enroll with Web3. Please try again."
      );
    } finally {
      if (button) {
        button.disabled = false;
        if (!button.classList.contains("btn-enrolled")) {
          button.innerHTML = '<i class="fab fa-ethereum"></i> Enroll with Web3';
        }
      }
    }
  }

  async signEnrollmentData(data) {
    try {
      const message = `Enroll in course ${data.course_id} at ${new Date(
        data.timestamp * 1000
      ).toISOString()}`;

      const signature = await this.provider.request({
        method: "personal_sign",
        params: [message, this.currentAccount],
      });

      return signature;
    } catch (error) {
      console.error("Error signing enrollment data:", error);
      if (error.code === 4001) {
        this.showError("Signature rejected by user.");
      } else {
        this.showError("Failed to sign enrollment data.");
      }
      return null;
    }
  }

  formatAddress(address) {
    if (!address) return "";
    return `${address.slice(0, 6)}...${address.slice(-4)}`;
  }

  showSuccess(message) {
    this.showMessage(message, "success");
  }

  showError(message) {
    this.showMessage(message, "error");
  }

  showMessage(message, type = "info") {
    // Create or get message container
    let messageContainer = document.getElementById("web3-messages");
    if (!messageContainer) {
      messageContainer = document.createElement("div");
      messageContainer.id = "web3-messages";
      messageContainer.style.position = "fixed";
      messageContainer.style.top = "20px";
      messageContainer.style.right = "20px";
      messageContainer.style.zIndex = "10000";
      document.body.appendChild(messageContainer);
    }

    // Create message element
    const messageEl = document.createElement("div");
    messageEl.className = `web3-message web3-message-${type}`;
    messageEl.style.cssText = `
            background: ${type === "error" ? "#dc2626" : "#059669"};
            color: white;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            word-wrap: break-word;
        `;
    messageEl.textContent = message;

    messageContainer.appendChild(messageEl);

    // Animate in
    setTimeout(() => {
      messageEl.style.transform = "translateX(0)";
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
      messageEl.style.transform = "translateX(100%)";
      setTimeout(() => {
        if (messageEl.parentNode) {
          messageEl.parentNode.removeChild(messageEl);
        }
      }, 300);
    }, 5000);
  }

  // Utility methods
  isWalletConnected() {
    return this.isConnected && this.currentAccount;
  }

  getCurrentAccount() {
    return this.currentAccount;
  }

  getChainId() {
    return this.chainId;
  }
}

// Initialize Web3 Wallet Manager
let web3Wallet;
document.addEventListener("DOMContentLoaded", () => {
  web3Wallet = new Web3WalletManager();
  window.web3Wallet = web3Wallet; // Make it globally accessible
});
