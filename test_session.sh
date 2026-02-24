#!/bin/bash

# Script de prueba para validar el sistema de sesión única
# Asegúrate de reemplazar las credenciales con datos válidos

BASE_URL="https://app.zondaerp.mx/api"
EMAIL="tu-email@ejemplo.com"
PASSWORD="tu-password"

echo "🧪 Iniciando prueba de sesión única..."
echo ""

# 1. Login inicial
echo "1️⃣ Haciendo login inicial..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.token')
USER_ID=$(echo $LOGIN_RESPONSE | jq -r '.userId')

echo "   ✅ Login exitoso"
echo "   👤 User ID: $USER_ID"
echo "   🔑 Token: ${TOKEN:0:20}..."
echo ""

# 2. Hacer una petición válida
echo "2️⃣ Probando petición con token válido..."
ORDERS_RESPONSE=$(curl -s -X GET "$BASE_URL/orders/$USER_ID/2025-01-15" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json")

if echo "$ORDERS_RESPONSE" | jq -e '.orders' > /dev/null 2>&1; then
  echo "   ✅ Petición exitosa con token válido"
else
  echo "   ❌ Error en petición:"
  echo "$ORDERS_RESPONSE" | jq '.'
fi
echo ""

# 3. Simular login en otro dispositivo
echo "3️⃣ Simulando login en otro dispositivo..."
LOGIN2_RESPONSE=$(curl -s -X POST "$BASE_URL/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

NEW_TOKEN=$(echo $LOGIN2_RESPONSE | jq -r '.token')
echo "   ✅ Segundo login exitoso"
echo "   🔑 Nuevo Token: ${NEW_TOKEN:0:20}..."
echo ""

# 4. Intentar usar el primer token (debe fallar)
echo "4️⃣ Intentando usar el primer token (debería fallar)..."
OLD_TOKEN_RESPONSE=$(curl -s -X GET "$BASE_URL/orders/$USER_ID/2025-01-15" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json")

if echo "$OLD_TOKEN_RESPONSE" | jq -e '.code' | grep -q "SESSION_EXPIRED"; then
  echo "   ✅ Token antiguo rechazado correctamente"
  echo "   📋 Mensaje: $(echo $OLD_TOKEN_RESPONSE | jq -r '.message')"
else
  echo "   ⚠️ Respuesta inesperada:"
  echo "$OLD_TOKEN_RESPONSE" | jq '.'
fi
echo ""

# 5. Usar el nuevo token (debe funcionar)
echo "5️⃣ Usando el nuevo token (debería funcionar)..."
NEW_TOKEN_RESPONSE=$(curl -s -X GET "$BASE_URL/orders/$USER_ID/2025-01-15" \
  -H "Authorization: Bearer $NEW_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json")

if echo "$NEW_TOKEN_RESPONSE" | jq -e '.orders' > /dev/null 2>&1; then
  echo "   ✅ Nuevo token funciona correctamente"
else
  echo "   ❌ Error con nuevo token:"
  echo "$NEW_TOKEN_RESPONSE" | jq '.'
fi
echo ""

# 6. Logout
echo "6️⃣ Cerrando sesión..."
LOGOUT_RESPONSE=$(curl -s -X POST "$BASE_URL/logout" \
  -H "Authorization: Bearer $NEW_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json")

if echo "$LOGOUT_RESPONSE" | jq -e '.message' | grep -q "correctamente"; then
  echo "   ✅ Logout exitoso"
else
  echo "   ⚠️ Respuesta de logout:"
  echo "$LOGOUT_RESPONSE" | jq '.'
fi
echo ""

echo "🏁 Prueba completada"
