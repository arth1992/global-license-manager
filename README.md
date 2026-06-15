# 🔐 Global License Manager (GLM)

The **Global License Manager** is the secure administrative portal and licensing verification backend for the **Global Admission Manager (GAM)** platform. It handles handshake requests, signs production license payloads cryptographically using **Ed25519** signatures, tracks client check-ins, and manages software releases.

---

## 🛠️ Local Development Setup

To run the License Manager locally:

1. **Install Dependencies**:
   ```bash
   composer install
   npm install
   ```

2. **Configure Environment**:
   Copy `.env.example` to `.env` and configure your local SQLite/MySQL settings:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Setup**:
   Create the SQLite/MySQL database and run migrations:
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

4. **Launch Development Servers**:
   Run the Laravel server and Vite assets simultaneously:
   ```bash
   npm run dev
   ```

---

## 🌐 Production Cloud Server Deployment

For deploying the Global License Manager to a production server (Ubuntu VPS), we provide a comprehensive step-by-step setup guide covering initial system hardening, packages installation, Nginx config, SSL setup, and background workers configuration.

> [!TIP]
> 📖 Refer to the detailed **[Cloud Server Deployment Guide](file:///c:/global-license-manager/docs/cloud_server_deployment_guide.md)** to configure and secure your production instance.

---

## 🔒 Cryptographic Licensing Architecture

The licensing verification uses asymmetric cryptography:
* **Ed25519 Private Key**: Stored securely in the `.env` of this license manager server. Used to sign license payloads.
* **Ed25519 Public Key**: Distributed to and embedded within each **Global Admission Manager** client instance. Used to verify the integrity and origin of the license payload.
* Critical verification routes:
  * `POST /api/v1/handshake`: Handles registration of new instances.
  * `POST /api/v1/verify`: Verifies active licenses against signatures.
  * `POST /api/v1/update/check`: Serves release checks for client updates.
