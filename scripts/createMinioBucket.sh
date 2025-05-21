#!/bin/bash

# Variables - dostosuj w razie potrzeby
MINIO_ENDPOINT="http://localhost:9000"
MINIO_ALIAS="local"
MINIO_USER="sail"
MINIO_PASS="password"
BUCKET_NAME="saasbase"

# Sprawdź, czy mc jest zainstalowany
if ! command -v mc &> /dev/null; then
    echo "Instalacja mc (MinIO Client)..."
    # Pobranie i instalacja mc
    curl -O https://dl.min.io/client/mc/release/linux-amd64/mc
    chmod +x mc
    sudo mv mc /usr/local/bin/
fi

echo "Konfiguracja mc aliasu..."
mc alias set $MINIO_ALIAS $MINIO_ENDPOINT $MINIO_USER $MINIO_PASS

echo "Tworzenie bucketa: $BUCKET_NAME..."
mc mb $MINIO_ALIAS/$BUCKET_NAME

echo "Bucket $BUCKET_NAME został utworzony (lub już istniał)."
