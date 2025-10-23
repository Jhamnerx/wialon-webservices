![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.001.png)

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.002.png)

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.003.png)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.004.png)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.005.png)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.006.png)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.007.png)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.008.png)

**TABLA DE CONTENIDO**

1. [PLATIN EMV AP](#_bookmark0)I

1. [REQUERIMIENTOS PREVIOS](#_bookmark1)

    1. [REGISTRO DE LA EMV](#_bookmark2)
    1. [TOKEN DE SEGURIDAD](#_bookmark3)

1. [USO DEL API](#_bookmark4)

    1. [ENDPOINT](#_bookmark5)
    1. [EJEMPLO](#_bookmark6)S
    1. ERORRES
    1. [RECOMENDACIONES](#_bookmark7)
    1. [NOTAS](#_bookmark7) DE INTERÉS

1. [EXTRA](#_bookmark8)

1. [GLOSARIO](#_bookmark9)

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.009.png)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.010.jpeg)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.011.png)![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.012.png)

# <a name="_bookmark0"></a>**PLATIN EMV API**

Este documento tiene por objetivo guiar a las [EMV](#_bookmark15) a implementar la retransmisión de sus [TRAMAS](#_bookmark14) hacia el servicio[API](#_bookmark10) [REST](#_bookmark11) de OSINERGMIN: [PLATIN](#_bookmark15)

## <a name="_bookmark1"></a>**REQUERIMIENTOS PREVIOS**

### <a name="_bookmark2"></a>**REGISTRO DE LA EMV**

Verifique si su [EMV](#_bookmark15) está registrado en la [PLATIN](#_bookmark15) , para esto puede usar los números de contacto en este documento, de lo contrario solicite el registro y siga los pasos que el operador le brindará para su registro.

### <a name="_bookmark3"></a>**TOKEN DE SEGURIDAD**

Para poder retransmitir una [TRAMA](#_bookmark14) hacia la [PLATIN](#_bookmark15) es necesario que tenga el [TOKEN](#_bookmark12) de seguridad asignada a su [EMV](#_bookmark15), si no lo tiene, puede comunicarse a los números de soporte y validando su información, le pueden enviar su [TOKEN](#_bookmark12).

## <a name="_bookmark4"></a>**USO DEL API**

Las [TRAMAS](#_bookmark14) deben ser enviadas vía [API](#_bookmark10) [REST](#_bookmark11) usando la siguiente información:

### <a name="_bookmark5"></a>**ENDPOINT**

#### **Datos del [**ENDPOINT**](#\_bookmark13)**

URL https://prod.osinergmin-agent-2021.com

![ref1]

HEADER Content-Type: application/json

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.014.png)

POST /api/v1/trama Envío de información de trama GPS

![ref1]

POST /api/v1/trama-batch Envío de información de tramas GPS en lote

![ref1]

#### **Parámetros del [**ENDPOINT**](#\_bookmark13)**

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.015.png)

| event   | OBLIGATORIO | STRING | Evento del vehículo. Ver lista de valores aceptados | acc_on                                 |
| :------ | :---------: | :----- | :-------------------------------------------------- | :------------------------------------- |
| gpsDate | OBLIGATORIO | DATE   | FechaHora del GPS del vehículo                      | <p>2021-10-</p><p>18T23:05:45.395Z</p> |
| plate   | OBLIGATORIO | STRING | Placa del vehículo                                  | ABC-123                                |
| speed   | OBLIGARORIO | DOUBLE | Velocidad del vehículo                              | 90                                     |

| <p>![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.016.png)**NOMBRE**</p><p></p><p></p><p>position</p> | <p>**MODO**</p><p></p><p></p><p>OBLIGATORIO</p> | <p>**TIPO**</p><p></p><p></p><p>JSON</p> | <p>**DESCRIPCIÓN**</p><p></p><p></p><p>Posición del vehículo</p> | <p>**EJEMPLO**</p><p>{"latitude": - 12.087457443458652,</p><p>“longitude": - 77.06647396087648,</p><p>“altitude”: - 77.154548784848}</p> |
| :----------------------------------------------------------------------------------------------------------- | :---------------------------------------------- | :--------------------------------------- | :--------------------------------------------------------------- | :--------------------------------------------------------------------------------------------------------------------------------------- |
| tokentrama                                                                                                   | OBLIGATORIO                                     | STRING                                   | Token de la EMV                                                  | EE03BFA4-84AD-4216- AC42-5581K3SE231F18                                                                                                  |
| odometer                                                                                                     | OBLIGATORIO                                     | STRING                                   | Odómetro del vehículo en Km                                      | 83605                                                                                                                                    |
| UUID                                                                                                         | OPCIONAL                                        | STRING                                   | Identificador usado en el endpoint batch                         | <p>4d8fee39-9578-4682-b04f-</p><p>a038dbd4f670</p>                                                                                       |

**Lista de valores aceptados para event:**

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.017.png)

none Ningún evento; vacío también es aceptado

![ref1]

acc_on Vehículo encendido

![ref1]

acc_off Vehículo apagado

![ref1]

battery_dc Batería desconectada

![ref1]

battery_ct Batería conectada

![ref1]

sos Botón de pánico

![ref1]

### <a name="_bookmark6"></a>**EJEMPLOS**

Para un ejemplo práctico, usaremos el comando CURL para poder enviar una trama a la [PLATIN](#_bookmark15), cabe recordar que los datos solo son de ejemplo, y cada [EMV](#_bookmark15) debe enviar sus propios datos.

#### **Ejemplo de Envío Individual**

En este caso enviaremos el siguiente cuerpo:

{

"event": "none",

"plate": "F5U-784",

"speed": 110,

"position": {

"latitude": -12.087457443458652,

"longitude": -77.06647396087648,

"altitude": 77.154548784848

},

"gpsDate": "2021-09-24T19:14:10.225Z",

"tokenTrama": "EE089GA4-888A-4216-AC42-53683E236F18",

"odometer": "83605"

}

Ejecutamos:

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.018.png)

Y la [PLATIN](#_bookmark15) nos debe responder lo siguiente, con código HTTP 200 - OK:

{

"timestamp":"2021-10-18T23:47:45.886+00:00 ",

"message":"El registro se ha creado con éxito", "status":"CREATED"

}

#### **Ejemplo de Envío en Lote**

En este caso enviaremos el siguiente cuerpo:

[

{

"event": "none",

"plate": "F5U-784",

"speed": 110,

"position": {

"latitude": -12.087457443458652,

"longitude": -77.06647396087648,

"altitude": 77.154548784848

},

"gpsDate": "2021-09-24T19:14:10.225Z",

"tokenTrama": "EE089GA4-888A-4216-AC42-53683E236F18",

"odometer": "83605",

"uuid": "4d8fee39-9578-4682-b04f-a038dbd4f670"

},

{

"event": "none",

"plate": "MU7-196",

"speed": 10,

"position": {

"latitude": -12.087457443458652,

"longitude": -77.06647396087648,

"altitude": 77.154548784848

},

"gpsDate": "2021-09-24T19:16:10.225Z",

"tokenTrama": "EE089GA4-888A-4216-AC42-53683E236F18",

"odometer": "54005",

"uuid": "b4961cba-fd7a-40ba-852c-ed5df9407357"

}

]

Ejecutamos:

curl -X POST "https://prod.osinergmin-agent-2021.com/api/v1/trama-batch"

--header 'Content-Type: application/json'

--data-raw '[{ "event": "None", "plate": "F5U-784", "speed": 110,

"position": {"latitude": -12.087457443458652, "longitude": -77.06647396087648,

“altitude”: 77.154548784848},

"gpsDate": "2021-09-24T19:14:10.225Z", "tokenTrama": "EEE089GA4-888A-4216- AC42-53683E236F18", “odometer”: “83605”, “uuid”: “4d8fee39-9578-4682-b04f-

a038dbd4f670” },

{ "event": "None", "plate": "MU7-196", "speed": 10,

"position": {"latitude": -12.087457443458652, "longitude": -77.06647396087648,

“altitude”: 77.154548784848},

"gpsDate": "2021-09-24T19:16:10.225Z", "tokenTrama": "EEE089GA4-888A-4216-

AC42-53683E236F18", “odometer”: “83605”, “uuid”: “b4961cba-fd7a-40ba-852c- ed5df9407357” }]'

Y la [PLATIN](#_bookmark15) nos debe responder lo siguiente, con código HTTP 207 – Multi Status:

{

"timestamp":"2021-10-18T23:47:45.886+00:00",

"data": [

{

},

{

sistema",

}

],

"message": "El registro se ha creado con éxito", "uuid": "4d8fee39-9578-4682-b04f-a038dbd4f670", "status": "CREATED”,

"uuid": "b4961cba-fd7a-40ba-852c-ed5df9407357",

"message": "el valor del campo plate (MU7-196) no existe en nuestro

"suggestion": "llame a soporte para dar de alta o habilitar la placa”, "status": "ERROR”

"metadata": {

"failure": 1,

"success": 1,

"total": 2

}

}

### **ERRORES**

[PLATIN](#_bookmark15) puede devolver los siguientes códigos de errores HTTP:

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.019.png)

422 DATOS ENVIADOS CON ERROR EN SU CONTENIDO

![ref1]

401 NO AUTORIZADO

![ref1]

50x ERROR DE SERVIDOR

![ref1]

### <a name="_bookmark7"></a>**RECOMENDACIONES**

Para evitar posibles problemas en caso de fallo en el envío de sus datos, nuestra recomendación es que todos los mensajes que devuelvan error, según lo indicado en la sección de ERRORES, sean reenviados luego de un tiempo; por ejemplo: luego de 1 / 3 / 6 / 12 horas.

### **NOTAS DE INTERÉS**

Cabe señalar que el servidor de la PLATIN está preparado para recibir todas las tramas que se envíen a través del servicio con el que cuenten todas las EMV; teniendo como premisa esto, se hace hincapié en que toda trama emitida desde el dispositivo GPS de una unidad vehicular debe retransmitirse a nuestro servidor, aun cuando dichas geolocalizaciones sean recibidas en los servidores de las EMV con desfase de tiempo.

Asimismo, contamos con todo el soporte necesario para la recepción correcta de tramas, y también las respuestas generadas a causa de las tramas que no están siendo ingestadas por un algún error en la retransmisión; con ello aseguramos tener una alternativa de solución ante cualquier incidente.

Además, se recomienda que cada EMV programe su retransmisión de tal manera que se evite reenviar tramas ya recepcionadas por nuestro servidor.

| <a name="_bookmark8"></a>**EXTRA** |            |
| :--------------------------------- | :--------- |
| VERSIÓN                            | 3\.2       |
| FECHA                              | 2022-03-31 |

![ref1]

# <a name="_bookmark9"></a>**GLOSARIO**

-   <a name="_bookmark10"></a>**API**: Conjunto de ENDPOINTS.

-   <a name="_bookmark11"></a>**REST**: Interfaz HTTP estándar para envío y recepción de información.
-   **ENDPOINT**: Recurso WEB al cual se le consulta o envía información.

-   <a name="_bookmark12"></a>**TOKEN**: Una cadena de texto que debe ser resguardada por cada E<a name="_bookmark13"></a>MV, se le considera con la misma sensibilidad de seguridad que una contraseña.
-   <a name="_bookmark14"></a>**TRAMA**: Información que contiene los valores que se reciben desde los GPS.

-   <a name="_bookmark15"></a>**EMV**: Empresa de Monitoreo Vehicular, es el proveedor contratado por el responsable de la(s) unidad(es) de transporte, que brinda el servicio de control satelital de unidades de transporte a través de los Sistemas de Posicionamiento Global (GPS) y envían la información de los GPS a OSINERGMIN.

-   **PLATIN**: Plataforma de Interoperabilidad de Supervisión GPS de OSINERGMIN que recibe y procesa la información enviada por las EMV.

Para cualquier duda y/o consulta adicional, por favor contactar a:

![Logotipo

Descripción generada automáticamente con confianza media](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.020.png)

![](Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.021.png)

[ref1]: Aspose.Words.0ae459c6-909e-49af-8fb8-eb02a3ae2537.013.png
