# glitter-analytics

> Privacy-first analytics module for GnuBoard7.

`glitter-analytics` is an analytics module for **GnuBoard7** that provides
dashboard statistics, visit history, and CSV export while minimizing the
collection of personally identifiable information.

> **Status:** Open Beta (v0.7.1)

---

## Features

- 📊 Dashboard analytics
- 👥 Visit history
- 📈 Daily statistics
- 📄 CSV export
- 🔒 Privacy-first data collection
- 🔑 IP hashing
- 🧹 Query string stripping
- 🌐 Referrer normalization
- 🤖 Bot filtering
- 🚫 Do Not Track (DNT) support
- 🛡️ Permission-based administration

---

## Privacy

glitter-analytics is designed with privacy in mind.

Collected data is minimized wherever possible.

Examples include:

- Hashed IP addresses
- Normalized referrer hosts
- Query string removal
- No sensitive request data storage

---

## Requirements

- PHP 8.3+
- Laravel 12
- GnuBoard7

---

## Installation

Install the module through the GnuBoard7 module system.

---

## Development Status

Current release:

**v0.7.1 — Open Beta**

The module is suitable for early production deployments while additional
automated regression tests continue to be expanded before the 1.0 stable release.

---

## License

MIT License
