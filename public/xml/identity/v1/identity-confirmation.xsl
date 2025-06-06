<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:ns="https://saasbase.madeyski.org/xml/identity/v1"
  exclude-result-prefixes="ns">

  <xsl:output method="html" encoding="UTF-8"/>

  <xsl:template match="/">
    <html>
      <head>
        <title>Oświadczenie tożsamości</title>
        <style>
          body {
            font-family: sans-serif;
            margin: 2em;
            color: #222;
          }
          .identity-box {
            border: 1px solid #ccc;
            padding: 1.5em;
            border-radius: 8px;
            box-shadow: 0 0 10px #ddd;
            max-width: 700px;
          }
          h1 {
            font-size: 1.8em;
            margin-bottom: 0.5em;
            color: oklch(58.8% 0.158 241.966);
          }
          p.description {
            margin-bottom: 2em;
            font-size: 1em;
            color: #555;
          }
          table {
            width: 100%;
            border-collapse: collapse;
          }
          th, td {
            padding: 0.6em 0.8em;
            border-bottom: 1px solid #ccc;
            text-align: left;
            vertical-align: top;
          }
          th {
            width: 35%;
            font-weight: bold;
            color: oklch(58.8% 0.158 241.966);
          }
          p.footer {
            font-size: 0.8em;
            color: #666;
            margin-top: 2em;
          }
          p.signature-note {
            font-style: italic;
            margin-top: 1em;
            color: #666;
            font-size: 0.9em;
          }
        </style>
      </head>
      <body>
        <div class="identity-box">
          <h1>Oświadczenie tożsamości</h1>
          <p class="description">
            Niniejszy dokument stanowi potwierdzenie tożsamości osoby fizycznej, wygenerowane przez system <strong>SaaSBase</strong>.
          </p>

          <table>
            <tr><th>Imię:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:FirstName"/></td></tr>
            <tr><th>Nazwisko:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:LastName"/></td></tr>
            <tr><th>Pełne imię i nazwisko:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:FullName"/></td></tr>
            <tr><th>Data urodzenia:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:BirthDate"/></td></tr>
            <tr><th>PESEL:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:PESEL"/></td></tr>
            <tr><th>Data wygenerowania:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:GeneratedAt"/></td></tr>
            <tr><th>Token potwierdzający:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:ConfirmationToken"/></td></tr>
            <tr><th>Nazwa aplikacji:</th><td><xsl:value-of select="ns:IdentityConfirmation/ns:ApplicationName"/></td></tr>
          </table>

          <p class="footer">
            Dokument wygenerowany automatycznie – nie wymaga podpisu ręcznego.
          </p>

          <p class="signature-note">
            <em>Dokument może zostać podpisany elektronicznie zgodnie z art. 20c ustawy o informatyzacji działalności podmiotów realizujących zadania publiczne.</em>
          </p>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
