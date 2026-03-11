# Tappy Cashback Pro

Plugin de cashback para WooCommerce que gera crédito quando o pedido é concluído e aplica o saldo automaticamente no checkout.

## Requisitos
- WordPress 6.0+
- WooCommerce 4.0+ (testado até 10.6)
- PHP 7.4+

## Recursos
- Geração automática de cashback ao mudar o pedido para **Concluído**
- Porcentagem global configurável
- Validade opcional em dias
- Aplicação automática do saldo no checkout
- Antifraude: cancela cashback em pedidos reembolsados/cancelados
- Histórico completo no **Minha Conta → Cashback**
- Tabela dedicada no banco de dados (`wp_tappy_cashback`)
- Compatível com HPOS
- Otimizado para Redis/Object Cache (cache de saldo)
- Expiração automática via cron com intervalo configurável (1h, 3h, 6h, 12h ou 24h)
- Interface administrativa usando WP_List_Table
- Controle de concorrência para evitar geração duplicada

## Como funciona
1. Cliente realiza um pedido.
2. Ao mudar para **Concluído**, o plugin calcula o cashback com base na porcentagem configurada.
3. O valor é salvo em `wp_tappy_cashback`, respeitando a validade (se definida).
4. No próximo checkout, o saldo é aplicado automaticamente como desconto até o limite do pedido.
5. Ao usar o saldo, os lançamentos são marcados como usados e o cache é limpo.

## Configurações (WooCommerce → Configurações → Geral)
- Habilitar Cashback
- Porcentagem (%)
- Validade (dias) — vazio para não expirar
- Intervalo de verificação do cron (1h, 3h, 6h, 12h ou 24h)

## Minha Conta
- Endpoint: **/minha-conta/cashback**
- Exibe pedido, data, valor, valor usado, status e expiração (quando houver).

## Admin
- Menu: WooCommerce → Cashback
- Lista paginada com ID, usuário, pedido (link), valor, usado, status, expira em, criado em.

## Licença
GPLv3 – https://www.gnu.org/licenses/gpl-3.0.html
