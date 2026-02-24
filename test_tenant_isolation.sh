#!/bin/bash

# Script para verificar el control de tenant en la API

BASE_URL="https://app.zondaerp.mx/api"

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "🔐 Verificando Control de Tenant en API"
echo "========================================"
echo ""

# Solicitar credenciales
read -p "📧 Email del usuario 1 (Tenant A): " EMAIL1
read -sp "🔑 Password: " PASSWORD1
echo ""
read -p "📧 Email del usuario 2 (Tenant B): " EMAIL2
read -sp "🔑 Password: " PASSWORD2
echo ""
echo ""

# Login Usuario 1 (Tenant A)
echo "1️⃣ Login Usuario 1 (Tenant A)..."
LOGIN1_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL1\",\"password\":\"$PASSWORD1\"}")

TOKEN1=$(echo $LOGIN1_RESPONSE | jq -r '.token')
USER1_ID=$(echo $LOGIN1_RESPONSE | jq -r '.userId')

if [ "$TOKEN1" != "null" ]; then
  echo -e "${GREEN}✅ Login exitoso${NC}"
  echo "   User ID: $USER1_ID"
else
  echo -e "${RED}❌ Error en login${NC}"
  echo "$LOGIN1_RESPONSE" | jq '.'
  exit 1
fi
echo ""

# Login Usuario 2 (Tenant B)
echo "2️⃣ Login Usuario 2 (Tenant B)..."
LOGIN2_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL2\",\"password\":\"$PASSWORD2\"}")

TOKEN2=$(echo $LOGIN2_RESPONSE | jq -r '.token')
USER2_ID=$(echo $LOGIN2_RESPONSE | jq -r '.userId')

if [ "$TOKEN2" != "null" ]; then
  echo -e "${GREEN}✅ Login exitoso${NC}"
  echo "   User ID: $USER2_ID"
else
  echo -e "${RED}❌ Error en login${NC}"
  echo "$LOGIN2_RESPONSE" | jq '.'
  exit 1
fi
echo ""

# Obtener órdenes del Usuario 1
echo "3️⃣ Obteniendo órdenes del Usuario 1 (Tenant A)..."
ORDERS1_RESPONSE=$(curl -s -X GET "$BASE_URL/orders/$USER1_ID/2025-02-20" \
  -H "Authorization: Bearer $TOKEN1" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json")

PRODUCTS1_COUNT=$(echo $ORDERS1_RESPONSE | jq '[.orders[]?.services[]?.products[]?] | length')
PESTS1_COUNT=$(echo $ORDERS1_RESPONSE | jq '[.orders[]?.services[]?.pests[]?] | length')

echo -e "${GREEN}✅ Respuesta recibida${NC}"
echo "   📦 Productos disponibles: $PRODUCTS1_COUNT"
echo "   🐛 Plagas disponibles: $PESTS1_COUNT"

# Guardar IDs de productos del Tenant A
PRODUCTS1_IDS=$(echo $ORDERS1_RESPONSE | jq -r '[.orders[]?.services[]?.products[]?.id] | unique | .[]')
echo "   🔢 IDs de productos: $PRODUCTS1_IDS"
echo ""

# Obtener órdenes del Usuario 2
echo "4️⃣ Obteniendo órdenes del Usuario 2 (Tenant B)..."
ORDERS2_RESPONSE=$(curl -s -X GET "$BASE_URL/orders/$USER2_ID/2025-02-20" \
  -H "Authorization: Bearer $TOKEN2" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json")

PRODUCTS2_COUNT=$(echo $ORDERS2_RESPONSE | jq '[.orders[]?.services[]?.products[]?] | length')
PESTS2_COUNT=$(echo $ORDERS2_RESPONSE | jq '[.orders[]?.services[]?.pests[]?] | length')

echo -e "${GREEN}✅ Respuesta recibida${NC}"
echo "   📦 Productos disponibles: $PRODUCTS2_COUNT"
echo "   🐛 Plagas disponibles: $PESTS2_COUNT"

# Guardar IDs de productos del Tenant B
PRODUCTS2_IDS=$(echo $ORDERS2_RESPONSE | jq -r '[.orders[]?.services[]?.products[]?.id] | unique | .[]')
echo "   🔢 IDs de productos: $PRODUCTS2_IDS"
echo ""

# Verificar separación de datos
echo "5️⃣ Verificando aislamiento de tenants..."

# Convertir IDs a arrays
PRODUCTS1_ARRAY=($PRODUCTS1_IDS)
PRODUCTS2_ARRAY=($PRODUCTS2_IDS)

# Buscar IDs comunes
COMMON_IDS=()
for id1 in "${PRODUCTS1_ARRAY[@]}"; do
  for id2 in "${PRODUCTS2_ARRAY[@]}"; do
    if [ "$id1" == "$id2" ]; then
      COMMON_IDS+=("$id1")
    fi
  done
done

if [ ${#COMMON_IDS[@]} -eq 0 ]; then
  echo -e "${GREEN}✅ CORRECTO: No hay productos compartidos entre tenants${NC}"
  echo "   Los datos están correctamente aislados por tenant"
else
  echo -e "${RED}⚠️ ADVERTENCIA: Se encontraron ${#COMMON_IDS[@]} productos comunes${NC}"
  echo "   IDs comunes: ${COMMON_IDS[@]}"
  echo -e "${YELLOW}   Esto puede ser normal si ambos usuarios pertenecen al mismo tenant${NC}"
fi
echo ""

echo "🏁 Verificación completada"
echo ""
echo "📊 Resumen:"
echo "   Usuario 1: $PRODUCTS1_COUNT productos, $PESTS1_COUNT plagas"
echo "   Usuario 2: $PRODUCTS2_COUNT productos, $PESTS2_COUNT plagas"
