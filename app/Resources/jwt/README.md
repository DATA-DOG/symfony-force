# JWT keys

To regenerate:

    openssl genrsa -out private.pem -aes256 4096
    openssl rsa -pubout -in private.pem -out public.pem
