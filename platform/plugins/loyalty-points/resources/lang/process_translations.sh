#!/bin/bash

# This script helps identify the sections that need to be updated in each language file
# It's a helper to verify the edits have been made correctly

LANG_DIR="/Users/sangnguyen/workspace/loyalty-pro/platform/plugins/loyalty-pro/resources/lang"

LANGUAGES=("hu" "id" "it" "ja" "ka" "ko" "lt" "lv" "ms" "nl" "no" "pl" "pt" "pt_BR" "ro" "ru" "sk" "sl" "sr" "sv" "th" "tr" "uk" "vi" "zh" "zh_HK")

echo "Checking translation status for remaining languages..."
echo "=================================================="

for lang in "${LANGUAGES[@]}"; do
    file="$LANG_DIR/$lang/loyalty-pro.php"
    if [ -f "$file" ]; then
        # Check if bonus_points_section exists
        if grep -q "bonus_points_section" "$file"; then
            echo "✓ $lang - bonus section EXISTS"
        else
            echo "✗ $lang - bonus section MISSING"
        fi

        # Check if points_expired exists
        if grep -q "points_expired" "$file"; then
            echo "✓ $lang - expired field EXISTS"
        else
            echo "✗ $lang - expired field MISSING"
        fi

        # Check if earned_from_registration exists
        if grep -q "earned_from_registration" "$file"; then
            echo "✓ $lang - registration bonus EXISTS"
        else
            echo "✗ $lang - registration bonus MISSING"
        fi

        # Check if exceeds_max_redemption_percentage exists
        if grep -q "exceeds_max_redemption_percentage" "$file"; then
            echo "✓ $lang - max percentage error EXISTS"
        else
            echo "✗ $lang - max percentage error MISSING"
        fi

        # Check if expiry_section exists
        if grep -q "expiry_section" "$file"; then
            echo "✓ $lang - expiry section EXISTS"
        else
            echo "✗ $lang - expiry section MISSING"
        fi

        # Check if max_redemption_percentage exists
        if grep -q "max_redemption_percentage" "$file"; then
            echo "✓ $lang - max redemption % EXISTS"
        else
            echo "✗ $lang - max redemption % MISSING"
        fi

        echo "---"
    else
        echo "✗ $lang - FILE NOT FOUND"
        echo "---"
    fi
done

echo "=================================================="
echo "Check complete!"
