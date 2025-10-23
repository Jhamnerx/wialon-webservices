![](Aspose.Words.904ff3b4-943f-48f3-b4d1-b655b312bc1b.001.png)

/api/v1.0/transmisiones

URL Requeridos para Consumó

**Desarrollo	<https://ws03.sutran.ehg.pe/api/v1.0/transmisiones>**

**Producción	<https://ws03.sutran.gob.pe/api/v1.0/transmisiones>**

# **Sinopsis**
**Petición**





























![](Aspose.Words.904ff3b4-943f-48f3-b4d1-b655b312bc1b.002.png)

**Respuesta**







![](Aspose.Words.904ff3b4-943f-48f3-b4d1-b655b312bc1b.003.png)
# **Manejo de la Petición**
La petición debidamente formateada en JSON como minimo debe de cumplir con el siguiente equema Campos requeridos : plate,geo,direction,event,speed,time\_device

Campos opcionales  : imei

Propiedades de los campos

|**Parámetro**|**Campo**|**Tipo**|**Obligatorio**|**Observación**|
| :- | :- | :-: | :- | :-: |
|**Header**|access-token|uuid|\*\*\*\*\*\*\*\*\*\*\*\*\*\*\*|El token es el identificador de la Empresa de Monitoreo Vehicular|
|<p></p><p></p><p></p><p></p><p></p><p></p><p>**Body**</p>|plate|string|HEN123|Placa del vehículo, cadena de seis caracteres|
||geo|array|[-11.410890 , -76.9604001]|Arreglo de dos numeros [latitud y la longitud]|
||direction|Int|38|<p>Rumbo - Sentido del desplazamiento en grados.</p><p>Entero que debe de tener valores entre 0 y 360</p>|
||<p></p><p>event</p>|<p></p><p>string</p>|<p></p><p>ER/PA/BP</p>|Valor del evento|
|||||**BP**: Botón de pánico|
|||||**ER**: En ruta (a partir de la velocidad > 0 km/h)|
|||||**PA**: Parada (a partir de la velocidad = 0 km/h)|
||speed|int|50|Velocidad de desplazamiento km/h. Numero entero|
||time\_device|date|2023-04-13 10:47:00|<p>Fecha de envío de la posición del dispositivo GPS.</p><p>Cadena de fecha en formato YYYY-MM-DD HH:MM:SS esta hora</p><p>debera estar formateada en GMT-5 y no en UTC</p>|
||imei|int|123456789102356|Número de 15 digitos|








![](Aspose.Words.904ff3b4-943f-48f3-b4d1-b655b312bc1b.004.png)

En la capa de seguridad tenermos el campo access-token el cual se deberá de mandar en la cabecera de la petición, esta en formato UUID y es una cadena que sera generada por la EMV desde su consola grafica, debe de tener el valor directo **NO USANDO Authorization: Bearer** debiendo usar en su lugar **access-token**: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx

# **Manejo de respuestas**
Siempre el servicio Web entregara los campos code y result los cuales pueden tener los siguientes valores

|**code**|**result**|
| :- | :- |
|1400|Mala Petición revisar el formato del JSON tenga sintaxis correcta|
|1403|No se envio la cabecera access-token|
|1404|No existe el recurso|
|1501|El access\_token no valido|
|1502|Limite de tramas superado maximo 150|
|1503|Formato incorrecto|
|1504|No se registran transmisiones|
|1505|Accesss token no existe o inactivo|

Adicionalmente se tienen errores por tramas

|**code**|**result**|
| :- | :- |
|2100|Plate con errores|
|2200|Campo geo con errores|
|2300|Campo direction con errores|
|2400|Campo event con errores|
|2500|Campo speed con errores|
|2600|Campo time\_device con errores|
|2610|Campo time\_Devide fuera de rango|
|||

Ejemplo de trama con error





























![](Aspose.Words.904ff3b4-943f-48f3-b4d1-b655b312bc1b.005.png)

Dando la siguiente respuesta

























![](Aspose.Words.904ff3b4-943f-48f3-b4d1-b655b312bc1b.006.png)

` `}	


Se observa que se enviaron dos tramas de las cuales no se procesaron ninguna (0/2) en el arreglo error\_plates indica la fila del arreglo del error y el tipo de error. La primera con error en speed y el segundo en error en plate.

En caso que la petición entregue una respuesta de tipo TIME\_OUT es posible que la dirección IP de origen de la petición no se encuentre dentro de la lista de direcciones permitidas por el firewall, se puede agregar direcciones por medio de la interface gui de la EMV/ADM


**Soporte**

Por medio de [oti@sutran.gob.pe.](mailto:oti@sutran.gob.pe)
