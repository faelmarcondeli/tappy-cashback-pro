# tappy-cashback-pro Wocommerce Requires at least: 6.0 Tested up to: 6.9 Requires PHP: 7.4+ Requires Wordpress 4.0+ Requires WooCommerce 4.0+ Utilizes Wordpress 6.9 Utilizes WooCommerce 10.6 Utilizes Wordpress Theme Flastome: 3.20.5 Utilizes Melhorias no e-mail do Woocommerce Utilizes Woocommerce Índices de pesquisa de texto completo do HPOS Utilizes Woocommerce Cache Product Objects Utilizes LitespeedCache 7.7 License: GPLv3 License URI: https://www.gnu.org/licenses/gpl-3.0.html

O plugin gera cashback automaticamente quando pedidos são concluídos e permite que clientes utilizem o saldo diretamente no checkout.

Recursos:

✔ Geração automática de cashback
✔ Definição de porcentagem global
✔ Validade opcional do cashback
✔ Aplicação automática no checkout
✔ Controle antifraude (cancelamento em reembolso)
✔ Histórico completo de cashback
✔ Endpoint no My Account
✔ Tabela própria no banco de dados
✔ Compatível com HPOS
✔ Otimizado para Redis / Object Cache
✔ Sistema de expiração automática via cron
✔ Interface administrativa com WP_List_Table
✔ Controle de concorrência com lock de geração
✔ Permite configurar tempo de verificação do hook cada 1 hora, 3 horas, 6 horas, 12 horas ou 24 horas
✔ Permite definir o tempo de validade do cashback em dias


Funcionamento do Cashback:

Cliente realiza um pedido.
O pedido muda para Concluído.
O plugin calcula o cashback.
O valor é registrado na tabela wp_tappy_cashback.
O saldo fica disponível para uso no checkout.
Ao finalizar nova compra o cashback é consumido automaticamente.

Interface Administrativa

Menu:
WooCommerce → Cashback
Tela construída com:
WP_List_Table

Mostra:
ID
Usuário
Pedido
Valor
Valor usado
Status
Expiração
Data de criação
