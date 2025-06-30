# Certificates for ePUAP Validation

## Official Source
- [EuroCert Root Certificates and CRLs](https://eurocert.pl/en/root-certificates-and-crls/)

## Which Certificates You Need

### Your Certificate Details
- **Issuer:** `CN=Centrum Kwalifikowane EuroCert,O=EuroCert Sp. z o.o.,C=PL`
- **Valid from:** 2024-02-13 to 2027-02-12
- **Algorithm:** RSA with SHA-256

### Required CA Certificates

1. **Root Certificate (NCCert)**
   - New root: "Root certificate of National Certification Centre (NCCert)"
   - **Validity:** 09.12.2016 to 10.12.2039
   - **Download:** `.crt` file from EuroCert website

2. **Intermediate Certificate (EuroCert Qualified CA)**
   - Based on the validity period, you likely need:
     - **EuroCert QCA03**
       - Valid: 14.02.2017 to 15.02.2028
       - Download: `.der` file
     - **EuroCert QCA06**
       - Valid: 08.05.2025 to 09.05.2036
       - Download: `.der` file

## Conversion Commands

```bash
# Convert DER to PEM
openssl x509 -inform DER -in QCA06_Eurocert_2025.der -out eurocert_qca06.pem

# Convert CRT to PEM (if not already PEM)
openssl x509 -inform DER -in nccert2016.crt -out nccert_root.pem
# or, if already PEM:
cp nccert2016.crt nccert_root.pem
```

## Adding to CA Bundle

```bash
cat nccert_root.pem >> ca-bundle.pem
cat eurocert_qca03.pem >> ca-bundle.pem
cat eurocert_qca06.pem >> ca-bundle.pem
```
