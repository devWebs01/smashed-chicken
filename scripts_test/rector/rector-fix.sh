#!/bin/bash

# Rector Fix Script
# Script untuk menerapkan perubahan Rector

echo "=================================="
echo "  Rector Fix - Apply Changes"
echo "=================================="
echo ""

# Konfirmasi sebelum menerapkan
read -p "Apakah Anda yakin ingin menerapkan perubahan Rector? (y/N) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "✓ Dibatalkan"
    exit 0
fi

echo ""
echo "🔧 Menerapkan perubahan Rector..."
echo ""

# Jalankan rector
php vendor/bin/rector process

# Cek exit code
EXIT_CODE=$?

echo ""
echo "=================================="

if [ $EXIT_CODE -eq 0 ]; then
    echo "✓ Perubahan berhasil diterapkan"
    echo ""
    echo "📋 Perubahan:"
    git diff --stat
    echo ""
    echo "💾 Tips: Review perubahan dengan 'git diff' lalu commit"
else
    echo "✗ Terjadi error saat menerapkan perubahan"
fi

echo "=================================="

exit $EXIT_CODE
