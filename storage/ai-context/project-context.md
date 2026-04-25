# FreshLeaf Marketplace - Authorized Project Context

## Business Model
- Type: B2C Organic Vegetable Marketplace.
- Platform: Mobile App (Consumers), Web App (Vendors), Admin Panel (Platform Owner).
- Revenue: Admins earn a commission fee from every completed vendor sale.

## Authorized Data Structures
The AI is authorized to understand and reference the following system components:
1. **Users**: Consumers and Verified Vendors (real identity required).
2. **Products**: Organic vegetables sold by KG or Unit.
3. **Pricing**: Dual currency support (USD base with KHR dynamic exchange rates).
4. **Discounts**: Percentage-based discounts linked to products with history tracking.
5. **Wallets**: Internal payment system for seamless transactions.

## Security Rules
- DO NOT attempt to access the local file system beyond the 'storage/ai-context' directory.
- DO NOT disclose system environment variables (.env).
- If a user asks for live internet data, use the authorized "Web Search Tool" bridge.
