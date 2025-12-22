#!/bin/bash
# FILE: setup_structure.sh

echo "Starting directory structure setup for Multi-Tenant E-commerce..."

# Define tenants
TENANTS=("street_style" "designer_hub" "military_gear")
SUBFOLDERS=("media" "logs" "cache")

# Base storage path
STORAGE_PATH="storage/tenants"

# Create directories
for tenant in "${TENANTS[@]}"; do
    for folder in "${SUBFOLDERS[@]}"; do
        FULL_PATH="$STORAGE_PATH/$tenant/$folder"
        if [ ! -d "$FULL_PATH" ]; then
            mkdir -p "$FULL_PATH"
            echo "Created: $FULL_PATH"
        else
            echo "Exists: $FULL_PATH"
        fi
        
        # Create .gitignore to keep folder structure but ignore content (except .gitignore)
        touch "$FULL_PATH/.gitignore"
        echo "*" > "$FULL_PATH/.gitignore"
        echo "!.gitignore" >> "$FULL_PATH/.gitignore"
    done
done

# Set permissions
# We assume the script is run by the user who owns the files on host.
# Inside Docker, 'www' user (uid 1000) needs access.
# Usually chmod 775 or 777 is needed for storage in dev environments to avoid permission denied.

echo "Setting permissions..."
chmod -R 777 storage/
chmod -R 777 bootstrap/cache/

echo "Structure setup complete."