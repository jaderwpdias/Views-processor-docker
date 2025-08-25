#!/bin/bash

# Aguardar o banco de dados estar pronto
echo "Aguardando banco de dados..."
while ! nc -z $DB_HOST 3306; do
  sleep 1
done
echo "Banco de dados pronto!"

# Executar o coletor
php src/collector.php
