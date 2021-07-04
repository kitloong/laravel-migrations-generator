# Start SQL Server, start the script to create/setup the DB
chmod +x /db-init.sh
/db-init.sh & /opt/mssql/bin/sqlservr
