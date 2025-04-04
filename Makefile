# Defaults
SQL ?= database.sql
DBML ?= database.dbml
RDBMS ?= mysql

# Supported RDBMS options
SUPPORTED_RDBMS := mysql postgres

# Helper function to validate RDBMS
define check_rdbms
	@rdbms_valid=0; \
	for item in $(SUPPORTED_RDBMS); do \
		if [ "$(RDBMS)" = "$$item" ]; then rdbms_valid=1; fi; \
	done; \
	if [ "$$rdbms_valid" -ne 1 ]; then \
		echo "❌ Error: Unsupported RDBMS '$(RDBMS)'. Supported: $(SUPPORTED_RDBMS)"; \
		exit 1; \
	fi
endef

# Convert SQL to DBML
convertSqlToDbml:
	$(call check_rdbms)
	@if [ ! -f "$(SQL)" ]; then \
		echo "❌ Error: SQL file '$(SQL)' not found."; \
		exit 1; \
	fi
	sql2dbml $(SQL) --$(RDBMS) -o $(DBML)

# Convert DBML to SQL
convertDbmlToSql:
	$(call check_rdbms)
	@if [ ! -f "$(DBML)" ]; then \
		echo "❌ Error: DBML file '$(DBML)' not found."; \
		exit 1; \
	fi
	dbml2sql $(DBML) --$(RDBMS) -o $(SQL)
