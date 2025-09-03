<!--
  Enhanced README with animations, badges, and improved structure
-->

<div align="center">
  <img src="modules/gateways/esewaV2/logo.png" alt="Esewa WHMCS Logo" width="96" height="96" />
  
  <h1>WHMCS Esewa Payment Gateway (V2)</h1>


  <p>
    <a href="#license"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License: MIT"></a>
    <img src="https://img.shields.io/badge/Currency-NPR-0f9d58" alt="Currency: NPR">
    <img src="https://img.shields.io/badge/WHMCS-Module-29ABE2" alt="WHMCS Module">
    <img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg" alt="PRs welcome">
  </p>
</div>

---

This repository provides a WHMCS payment gateway module for integrating Esewa, a popular payment gateway in Nepal. The module enables seamless, secure payment processing in NPR for WHMCS-based platforms.

- Simple to install and configure
- Secure payment flow with callback handling
- Customizable and extendable
- Supports NPR (Nepalese Rupee)

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Folder Structure](#folder-structure)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)


## Installation

1. Download or clone this repository.
2. Copy the `modules` folder to the root of your WHMCS installation directory, merging with existing folders if prompted.
3. In WHMCS Admin, navigate to: `Setup` → `Payments` → `Payment Gateways`.
4. Activate the gateway named: `Esewa V2`.

## Configuration

In the gateway settings, be sure to:

- Enter your Esewa credentials as provided by Esewa.
- Set the option "Convert To For Processing" to `NPR`.
- Verify callback/return URLs are reachable from your WHMCS instance.

> Keep your WHMCS installation up to date to avoid compatibility issues.

## Folder Structure

<details>
<summary>Click to expand</summary>

```
modules/
  gateways/
    esewaV2.php
    callback/
      esewaV2.php
    esewaV2/
      helpers.php
      init.php
      logo.png
      whmcs.json
```

</details>

## Troubleshooting

- Payments not showing as paid in WHMCS:
  - Ensure the callback file `modules/gateways/callback/esewaV2.php` is accessible and not blocked by your server/firewall.
  - Double-check Esewa credentials and environment (sandbox vs production) if applicable.
- Currency mismatch or errors:
  - Confirm `NPR` is enabled in WHMCS and selected for "Convert To For Processing".
- Logs:
  - Check WHMCS gateway logs and PHP error logs for details.

## Contributing

Contributions are welcome! Please open an issue to discuss changes or submit a PR.

- Fork the repository
- Create a feature branch
- Commit with clear messages
- Open a pull request

## Support

- Open an issue with a clear description and reproduction steps.


## Developer

This module was developed by **Yubraj Pandeya**.

## License

MIT License. See the `LICENSE` file for details.
