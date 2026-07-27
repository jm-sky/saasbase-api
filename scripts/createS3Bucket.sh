#!/bin/bash

# Variables - adjust if needed
S3_ENDPOINT="http://localhost:9000"
S3_ALIAS="local"
S3_ACCESS_KEY="sail"
S3_SECRET_KEY="password"
BUCKET_NAME="saasbase"

# Check if mc is installed
if ! command -v mc &> /dev/null; then
    echo "Installing mc (S3 client)..."
    curl -O https://dl.min.io/client/mc/release/linux-amd64/mc
    chmod +x mc
    sudo mv mc /usr/local/bin/
fi

echo "Configuring mc alias..."
mc alias set $S3_ALIAS $S3_ENDPOINT $S3_ACCESS_KEY $S3_SECRET_KEY

echo "Creating bucket: $BUCKET_NAME..."
mc mb $S3_ALIAS/$BUCKET_NAME

echo "Bucket $BUCKET_NAME created (or already existed)."
