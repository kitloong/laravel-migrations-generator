# SQLite Alter Foreign Key

The generator first generates all tables and then adds foreign keys to existing tables.

However, SQLite only supports foreign keys upon creation of the table and not when tables are altered.
*_add_foreign_keys_* migrations will still be generated, however will get omitted if migrate to SQLite type database.
