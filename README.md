# Módulo Backup PayPal Plus para Magento 2
![](https://raw.githubusercontent.com/wiki/paypal/PayPal-PHP-SDK/images/homepage.jpg)

Utilize este módulo apenas caso o Engenheiro de Integração do PayPal tenha sugerido, caso contrário utilize este: https://github.com/br-paypaldev/magento2-module

## Descrição

PayPal Plus é a solução de checkout transparente do PayPal, ao qual o usuário consegue efetuar uma compra sem a necessidade de um login.

O PayPal Plus está disponível apenas para contas PayPal cadastradas com CNPJ (Conta Empresa), caso a sua conta seja de pessoa física, você deve abrir uma conta PayPal de pessoa jurídica por este link.

A solução requer aprovação comercial, entre em contato pelo 0800 721 6959 e solicite agora mesmo.

**O PayPal Plus só irá funcionar caso tenha sido aprovado pelo PayPal.**

## Requisitos

Para o correto funcionamento da solução, é necessário verificar que a sua loja e servidor suporte alguns recursos:
1. A sua loja precisa ter suporte ao TAX_VAT, portanto antes de ativar a solução garanta que a sua loja está devidamente configurada para suportar este campo;
2. O servidor precisa ter suporte à TLS 1.2 ou superior e HTTPS 1.1 [(Referência Oficial)](https://www.paypal.com/sg/webapps/mpp/tls-http-upgrade).
3. O servidor precisa ter suporte à PHP 7.0 ou superior;

**Habilitar o VAT Number no Front-end:**
- STORES -> Settings -> Configuration -> Customers -> Customer Configuration -> Create New Account Options -> Show VAT Number on Storefront (Habilitar como "Yes")

**Habilitar como obrigatório o Tax/VAT Number no endereço do Cliente:**
- STORES -> Settings -> Configuration -> Customers -> Customer Configuration -> Name and Address Options -> Show Tax/VAT Number	 (Habilitar como "Required")

## Dúvidas/Suporte

Caso a sua dúvida não tenha sido respondida aqui, entre em contato com o PayPal pelo número 0800 047 4482.

E caso necessite de algum suporte técnico e/ou acredita ter encontrado algum problema com este módulo acesse o nosso [portal de suporte técnico](https://www.paypal-support.com/s/?language=pt_BR) e abra um ticket detalhando o seu problema na seção "Fale Conosco".
