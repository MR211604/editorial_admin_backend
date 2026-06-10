<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <style>
    .content-box {
      padding: 20px;
      background-color: #ffffff;
      border-radius: 8px;
    }

    .label {
      font-weight: bold;
      color: #333333;
      margin-bottom: 5px;
      display: block;
    }

    .value {
      color: #555555;
      margin-bottom: 20px;
      padding: 10px;
      background-color: #f9f9f9;
      border-radius: 8px;
      border: 1px solid #eeee;
    }
  </style>
</head>

<body
  style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4;">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center" style="padding: 40px 0 30px 0;">
        <!-- Header con Logo -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600"
          style="background-color: #004a99; border-radius: 8px 8px 0 0; overflow: hidden;">
          <tr>
            <td align="center" style="padding: 30px 0 30px 0; color: #ffffff;">
              <h1 style="margin: 0; font-size: 24px;">¡Has recibido un nuevo mensaje!</h1>
            </td>
          </tr>
        </table>

        <!-- Contenido -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600"
          style="background-color: #ffffff; border-radius: 0 0 8px 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
          <tr>
            <td style="padding: 20px 30px;">
              <div class="content-box">
                <span class="label">Nombre del Remitente:</span>
                <div class="value">{{ $contactData['name'] }}</div>

                <span class="label">Correo Electrónico:</span>
                <div class="value">{{ $contactData['email'] }}</div>

                <span class="label">Asunto:</span>
                <div class="value">{{ $contactData['subject'] }}</div>

                <span class="label">Mensaje:</span>
                <div
                  style="padding: 15px; background-color: #f9f9f9; border: 1px solid #eeeeee; border-radius: 4px; color: #444444; line-height: 1.6;">
                  {!! nl2br(e($contactData['message'])) !!}
                </div>
              </div>
            </td>
          </tr>
          <tr>
            <td style="padding: 0 30px 40px 30px; color: #888888; font-size: 12px; text-align: center;">
              Este es un mensaje automático enviado desde el formulario de contacto del sitio web.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>

</html>