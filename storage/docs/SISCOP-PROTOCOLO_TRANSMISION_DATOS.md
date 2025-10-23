1. # <a name="_bookmark4"></a>**Procedimiento de Transmisión de datos GPS**
    Es importante destacar que toda la información enviada con el código 201 será almacenada en nuestros servidores sin que se elimine ningún punto. Se contemplan dos endpoints del SIPCOP-M diferentes para la transmisión de datos, las cuales son: 0. ### **Especificación para unidades móviles de serenazgo:**

<https://transmision.mininter.gob.pe/retransmisionGPS/swagger-ui.html>

-   **Endpoint para pruebas:** /puntosGPS (el IdMunicipalidad para las pruebas es 1234567890 en todos los casos)
-   **Endpoint para producción:** /ubicacionGPS

0. ### **Especificación para unidades móviles policiales:**

<https://transmision.mininter.gob.pe/retransmisionpolicial/swagger-ui.html>

-   **Endpoint para pruebas:** /puntosGPS (el IdTransmisión para las pruebas es 1234567890 en todos los casos)
-   **Endpoint para producción:** /ubicacion/gps-policial

# **Preparación de Datos**

0. ### **Recopilación:**

    0. El proveedor debe utilizar un dispositivo GPS con las siguientes características: 0. Precisión mínima: 10 metros. 0. Vehículo encendido: Transmitir geo-posición global cada 10 segundos 0. Vehículo apagado: Transmitir geo-posición global cada 3 minutos 0. Batería de respaldo : Mínimo 1 hora 0. Sensores de: Encendido de motor, Corte de batería externa, Movimiento. 0. Comunicación vía: GSM y/o GPRS y/o EDGE y/o UMTS. 0. Sistema de posicionamiento por satélites: GPS (NAVSTAR-GPS) y/o GLONASS y/o GALILEO. 0. Equipos GPS homologados por el MTC.

1. **Validación:** Antes de enviar los datos, se deben validar los siguientes aspectos: 0. Formato: Verificar que los datos cumplan con el formato especificado por el MININTER (e.g., JSON). 0. Campos obligatorios: Asegurar que todos los campos obligatorios de la trama estén presentes y contengan valores válidos (e.g., placa, imei, idtransmisión y ubigeo). 0. Rango de valores: Validar que los valores de los campos estén dentro de los rangos permitidos (e.g., latitud y longitud). 0. Integridad: Verificar la integridad de los datos mediante mecanismos como checksums.
2. ### **Construcción de la Trama:**
    0. Los datos validados deben ser organizados en una estructura JSON que siga el esquema definido por el MININTER para cada tipo de unidad móvil (serenazgo o policial).
    1. Ejemplo de estructura JSON para transmisiones vehículos serenazgo:

{

"alarma": "", "altitud": 2793,

"angulo": 314,

"distancia": 68485,

"fechaHora": "12/12/2020 07:42:50",

"horasMotor": 14,

"idMunicipalidad": "495268a2-acc0-45a8-bf7f-20dc61c64", "ignition":true,

"imei": "359632109283942",

"latitud":-7.1626, "longitud": -78.5189, "motion": true, "totalDistancia": 15,

"totalHorasMotor": 0,

"ubigeo": "060101",

"placa": "XXX-123",

"valid":true, "velocidad": 17

}

0. Tipos de datos para la estructura JSON serenazgo:

| **NOMBRE DEL DATO** | **TIPO DE DATO** | **TAMAÑO**                            | **REQUERIDO** |
| :------------------ | :--------------- | :------------------------------------ | :------------ |
| idMunicipalidad     | String           | Mín: 10, Máx: 36                      | Sí            |
| Imei                | String           | 15 caracteres                         | Sí            |
| fechaHora           | String           | Max: 19                               | Sí            |
| Latitud             | Double           | <p>Entero: 5,</p><p>Decimales: 15</p> | Sí            |
| Longitud            | Double           | <p>Entero: 5,</p><p>Decimales: 15</p> | Sí            |
| Altitud             | Double           | <p>Entero: 5,</p><p>Decimales: 15</p> | Sí            |
| Angulo              | Integer          | N/A                                   | Sí            |
| velocidad           | Float            | <p>Entero: 5,</p><p>Decimales: 2</p>  | Sí            |
| totalHorasMotor     | Double           | N/A                                   | Sí            |

| **NOMBRE DEL DATO** | **TIPO DE DATO** | **TAMAÑO**                           | **REQUERIDO** |
| :------------------ | :--------------- | :----------------------------------- | :------------ |
| horasMotor          | Double           | N/A                                  | Sí            |
| Distancia           | Double           | <p>Entero: 5,</p><p>Decimales: 2</p> | Sí            |
| totalDistancia      | Double           | N/A                                  | Sí            |
| motion              | Boolean          | N/A                                  | Sí            |
| valid               | Boolean          | N/A                                  | Sí            |
| ignition            | Boolean          | N/A                                  | Sí            |
| alarma              | String           | Máx: 200                             | Sí            |
| ubigeo              | String           | 6 caracteres                         | Sí            |
| placa               | String           | Mín: 6, Máx: 10                      | Sí            |

0. Ejemplo de estructura JSON para transmisiones vehículos Policiales:

{

"alarma": "string", "altitud": 11,

"angulo": 12,

"codigoComisaria": "150101",

"distancia": 13,

"fechaHora": "03/01/2024 17:55:00",

"horasMotor": 14,

"idTransmision": "8f4b46f9-7dee-4e2d-aad2-65e9b650ec41", "ignition": true,

"imei": "344334433420000",

"latitud": 10.04441,

"longitud": -20.4402, "motion": true, "placa": "XXX-123",

"totalDistancia": 15,

"totalHorasMotor": 16,

"ubigeo": "150118", "valid": true, "velocidad": 17

}

0. Tipos de datos para la estructura JSON Policial:

| **NOMBRE DEL DATO** | **TIPO DE DATO** | **TAMAÑO**                            | **REQUERIDO** |
| :------------------ | :--------------- | :------------------------------------ | :------------ |
| idTransmision       | String           | Mín: 10, Máx: 36                      | Sí            |
| imei                | String           | 15 caracteres                         | Sí            |
| fechaHora           | String           | Max: 19                               | Sí            |
| latitud             | Double           | <p>Entero: 5,</p><p>Decimales: 15</p> | Sí            |
| longitud            | Double           | <p>Entero: 5,</p><p>Decimales: 15</p> | Sí            |
| altitud             | Double           | <p>Entero: 5,</p><p>Decimales: 15</p> | Sí            |
| angulo              | Integer          | N/A                                   | Sí            |
| velocidad           | Float            | <p>Entero: 5,</p><p>Decimales: 2</p>  | Sí            |
| totalHorasMotor     | Double           | N/A                                   | Sí            |

| **NOMBRE DEL DATO** | **TIPO DE DATO** | **TAMAÑO**                           | **REQUERIDO** |
| :------------------ | :--------------- | :----------------------------------- | :------------ |
| horasMotor          | Double           | N/A                                  | Sí            |
| distancia           | Double           | <p>Entero: 5,</p><p>Decimales: 2</p> | Sí            |
| totalDistancia      | Double           | N/A                                  | Sí            |
| motion              | Boolean          | N/A                                  | Sí            |
| valid               | Boolean          | N/A                                  | Sí            |
| ignition            | Boolean          | N/A                                  | Sí            |
| alarma              | String           | Máx: 200                             | Sí            |
| ubigeo              | String           | 6 caracteres                         | Sí            |
| **placa**           | **String**       | **Mín: 6, Máx: 10**                  | **Sí**        |
