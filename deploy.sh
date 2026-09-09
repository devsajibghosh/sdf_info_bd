#!/bin/bash

# Output folder
OUTPUT_DIR="deploy"

# Clean old export
rm -rf "$OUTPUT_DIR"
mkdir -p "$OUTPUT_DIR"

# Get modified + untracked files (use git directly without weird regex)
FILES=$(git status --porcelain | awk '{print $2}')

# Copy files to output folder
for file in $FILES; do
    # Skip empty entries
    [ -z "$file" ] && continue

    mkdir -p "$OUTPUT_DIR/$(dirname "$file")"
    cp "$file" "$OUTPUT_DIR/$file"
done

echo "✅ Export complete. Files saved in: $OUTPUT_DIR"
