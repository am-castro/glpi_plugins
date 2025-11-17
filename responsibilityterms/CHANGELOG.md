# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2025-11-17

### Added
- Initial release
- Term templates with dynamic placeholders
- PDF generation for users
- Equipment linking (Computers, Phones, Lines)
- Digital signature API integration (Basic and Bearer auth)
- Multi-language support (EN, PT-BR)
- User tab for term management
- Configuration page for signature settings
- Status tracking (pending, sent, signed, rejected)

### Features
- Create reusable term templates
- Generate personalized PDFs linked to users
- Automatic equipment association
- View and download generated PDFs
- Send terms to digital signature platform
- Configurable signature API integration

### Technical
- GLPI 10.0.0+ compatibility
- Namespace: GlpiPlugin\ResponsibilityTerms
- Database tables with proper indexing
- Profile-based permissions
- CSRF protection
- Search options for all itemtypes
