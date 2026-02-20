# Documentación: Sistema de Sesión Única

## Descripción General

Se ha implementado un sistema de **sesión única** que garantiza que solo una cuenta puede estar activa a la vez en cualquier dispositivo. Cuando un usuario inicia sesión en un nuevo dispositivo, todas las sesiones anteriores se invalidan automáticamente.

## Componentes Implementados

### 1. Backend (zonda_erp)

#### Archivos Modificados:

- **`app/Http/Controllers/AppController.php`**
  - Método `login()`: Ahora revoca todos los tokens anteriores antes de crear uno nuevo
  - Método `logout()`: Limpia correctamente el `session_token` y revoca todos los tokens

- **`app/Http/Middleware/CheckSingleSession.php`**
  - Middleware actualizado para validar sesiones tanto web como API (Sanctum)
  - Detecta si un token fue revocado y rechaza la petición

- **`routes/api.php`**
  - Todas las rutas protegidas ahora usan el middleware `single.session`

### 2. Flujo de Funcionamiento

#### Al Iniciar Sesión:
```
1. Usuario envía credenciales a POST /api/login
2. Backend valida credenciales
3. Backend REVOCA todos los tokens anteriores del usuario
4. Backend crea un nuevo token de Sanctum
5. Backend guarda el nuevo token en session_token
6. Backend devuelve el token al cliente
```

#### Al Hacer Peticiones:
```
1. Cliente envía petición con Bearer token en headers
2. Middleware auth:sanctum valida el token
3. Middleware single.session verifica que el token no haya sido revocado
4. Si el token fue revocado (sesión en otro dispositivo):
   - Devuelve error 401 con código 'SESSION_EXPIRED'
5. Si el token es válido, permite continuar con la petición
```

#### Al Cerrar Sesión:
```
1. Usuario envía credenciales a POST /api/logout
2. Backend valida credenciales
3. Backend revoca todos los tokens del usuario
4. Backend limpia session_token (lo pone en null)
5. Usuario puede volver a iniciar sesión
```

## Implementación en la App Móvil (zonda_app)

### 1. Configurar Interceptor Axios

En `zonda_app/app/config/axios.ts`, necesitas agregar un interceptor para manejar el error de sesión expirada:

```typescript
import axios from 'axios';
import AsyncStorage from '@react-native-async-storage/async-storage';
import { router } from 'expo-router';

const api = axios.create({
  baseURL: 'https://tu-dominio.com/api',
  timeout: 30000,
});

// Interceptor para agregar el token a todas las peticiones
api.interceptors.request.use(
  async (config) => {
    const token = await AsyncStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar errores de sesión
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      const errorCode = error.response?.data?.code;
      
      // Si la sesión expiró por login en otro dispositivo
      if (errorCode === 'SESSION_EXPIRED') {
        // Limpiar almacenamiento local
        await AsyncStorage.multiRemove(['auth_token', 'user_data']);
        
        // Mostrar alerta al usuario
        Alert.alert(
          'Sesión Cerrada',
          'Tu cuenta ha iniciado sesión en otro dispositivo. Por favor, inicia sesión nuevamente.',
          [
            {
              text: 'Aceptar',
              onPress: () => {
                // Redirigir a login
                router.replace('/login');
              }
            }
          ]
        );
      }
    }
    return Promise.reject(error);
  }
);

export default api;
```

### 2. Actualizar el Login

En `zonda_app/app/login.tsx`:

```typescript
import api from './config/axios';

const handleLogin = async () => {
  try {
    const response = await api.post('/login', {
      email: email,
      password: password,
    });

    // Guardar token y datos del usuario
    await AsyncStorage.setItem('auth_token', response.data.token);
    await AsyncStorage.setItem('user_data', JSON.stringify(response.data));

    // Redirigir a la app
    router.replace('/(tabs)/orders');
  } catch (error) {
    if (error.response?.status === 401) {
      Alert.alert('Error', 'Credenciales incorrectas');
    } else {
      Alert.alert('Error', 'No se pudo iniciar sesión');
    }
  }
};
```

### 3. Actualizar el Logout

En `zonda_app/app/(tabs)/profile.tsx` (o donde tengas el logout):

```typescript
const handleLogout = async () => {
  try {
    const userData = await AsyncStorage.getItem('user_data');
    const user = JSON.parse(userData || '{}');

    // Llamar al endpoint de logout (requiere credenciales)
    // Nota: Este endpoint requiere email y password por seguridad
    await api.post('/logout', {
      email: user.email,
      password: password, // Pedir al usuario su contraseña al hacer logout
    });

    // Limpiar almacenamiento local
    await AsyncStorage.multiRemove(['auth_token', 'user_data']);

    // Redirigir a login
    router.replace('/login');
  } catch (error) {
    console.error('Error al cerrar sesión:', error);
    
    // Aun si falla el logout en el servidor, limpiar localmente
    await AsyncStorage.multiRemove(['auth_token', 'user_data']);
    router.replace('/login');
  }
};
```

### 4. Manejo de Estados de Sesión

Considera agregar un contexto global para manejar el estado de autenticación:

```typescript
// app/context/AuthContext.tsx
import React, { createContext, useState, useEffect } from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import api from '../config/axios';

export const AuthContext = createContext({});

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    loadStoredAuth();
  }, []);

  const loadStoredAuth = async () => {
    try {
      const token = await AsyncStorage.getItem('auth_token');
      const userData = await AsyncStorage.getItem('user_data');
      
      if (token && userData) {
        setUser(JSON.parse(userData));
      }
    } catch (error) {
      console.error('Error loading auth:', error);
    } finally {
      setLoading(false);
    }
  };

  const handleSessionExpired = async () => {
    await AsyncStorage.multiRemove(['auth_token', 'user_data']);
    setUser(null);
    router.replace('/login');
  };

  return (
    <AuthContext.Provider value={{ user, loading, handleSessionExpired }}>
      {children}
    </AuthContext.Provider>
  );
};
```

## Pruebas Recomendadas

1. **Login en Dispositivo A**
   - Iniciar sesión con un usuario
   - Verificar que se recibe el token correctamente

2. **Login en Dispositivo B con la misma cuenta**
   - Iniciar sesión con el mismo usuario en otro dispositivo
   - Verificar que se recibe un nuevo token

3. **Intentar usar el token anterior (Dispositivo A)**
   - Hacer cualquier petición desde el Dispositivo A
   - Debería recibir error 401 con código 'SESSION_EXPIRED'
   - La app debería mostrar el mensaje y redirigir a login

4. **Logout**
   - Cerrar sesión desde cualquier dispositivo
   - Intentar hacer peticiones
   - Debería recibir error 401

## Notas Importantes

1. **Seguridad**: El método `logout()` requiere email y password para evitar cierres de sesión maliciosos. Considera si quieres mantener esto o usar solo el token.

2. **Experiencia de Usuario**: Cuando un usuario es deslogueado automáticamente, asegúrate de mostrar un mensaje claro indicando que su cuenta fue usada en otro dispositivo.

3. **Sincronización**: Si tienes datos sin sincronizar cuando se cierra la sesión, considera implementar un sistema de cola para intentar sincronizar antes de cerrar completamente.

4. **Manejo de Errores**: El interceptor de axios debe manejar todos los errores 401 y verificar si es por sesión expirada o por falta de autenticación.

## Endpoints Afectados

Todos los endpoints bajo el middleware `single.session`:
- `/api/orders/{id}/{date}`
- `/api/user/getData`
- `/api/reports/handle`
- `/api/technician/customers`
- `/api/device/update-location`
- Y todos los demás dentro del grupo protegido

## Migración de Base de Datos

La columna `session_token` ya existe en la tabla `users`. No se requieren nuevas migraciones.

## Logs y Monitoreo

El sistema registra automáticamente en los logs cuando:
- Se invalidan tokens al hacer login
- Se detecta un intento de uso de token revocado
- Se cierra sesión correctamente

Puedes verificar estos logs en `storage/logs/laravel.log`
