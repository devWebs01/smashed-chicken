#!/bin/bash

# Rector Check Script
# Script untuk mengecek perubahan yang akan dilakukan Rector

echo "=================================="
echo "  Rector Dry-Run Check"
echo "=================================="
echo ""

# Jalankan rector dalam mode dry-run
php vendor/bin/rector process --dry-run

# Cek exit code
EXIT_CODE=$?

echo ""
echo "=================================="

if [ $EXIT_CODE -eq 0 ]; then
    echo "✓ Tidak ada perubahan yang diperlukan"
    echo "  Kode Anda sudah sesuai dengan standar Rector"
elif [ $EXIT_CODE -eq 2 ]; then
    echo "⚠ Ada perubahan yang akan dilakukan"
    echo "  Jalankan './rector-fix.sh' untuk menerapkan perubahan"
else
    echo "✗ Terjadi error saat menjalankan Rector"
fi

echo "=================================="

exit $EXIT_CODE
