# Sistema de Rastreamento de Pedidos e Roteirização Logística

Sistema moderno de rastreamento de entregas (Pedidos de Venda) e coletas (Pedidos de Compra) com integração em tempo real ao banco de dados do **TOTVS Protheus (SQL Server)**, controle de fluxos logísticos operacionais, simulação de leitura de QR Code em rota com captura de geolocalização e dashboards gerenciais.

---

## 🚀 Arquitetura e Tecnologias

- **Framework**: Laravel 11 (PHP 8.4-fpm)
- **Banco de Dados Local**: MySQL 8.0 (persiste as operações do painel logístico)
- **Banco de Dados ERP**: Microsoft SQL Server (MP_12 - Tabelas do Protheus)
- **Infraestrutura**: Docker (Nginx, PHP com drivers Microsoft ODBC compilados e MySQL)
- **Estilização**: CSS Vanilla com design responsivo (Dark/Glassmorphism) e ícones Bootstrap

---

## 📦 Estrutura de Tabelas Protheus Consultadas (Read-Only)

- **Pedido de Venda (Cabeçalho)**: Tabela `SC5010` (Chave `C5_NUM`)
- **Pedido de Venda (Itens)**: Tabela `SC6010` (Chave `C6_NUM`)
- **Pedido de Compra**: Tabela `SC7010` (Chave `C7_NUM`)

> [!NOTE]
> Todas as consultas de integração do Protheus filtram automaticamente registros deletados logicamente (`D_E_L_E_T_ = ' '`). O sistema conta com fallback inteligente de dados simulados (mock) caso o banco do Protheus esteja offline.

---

## 🛠️ Instalação e Execução

### 1. Iniciar os Containers Docker
Certifique-se de que sua VPN local está ativa se for testar a conexão real com o Protheus.
```bash
docker compose up -d --build
```

### 2. Executar as Migrações e Seeds (Banco Local)
Este comando criará o esquema do banco de dados local do dashboard e carregará os usuários padrão para testes operacionais:
```bash
docker compose exec app php artisan migrate:fresh --seed
```

### 3. Acesso à Aplicação
- **URL da Aplicação**: [http://localhost:8080](http://localhost:8080)
- **Área do Cliente (Pública)**: Acessível diretamente na raiz `/`.
- **Painel Interno (Login)**: Acessível na rota `/login` (ou através do link no portal do cliente).

---

## 👥 Credenciais de Teste (Senha Padrão: `password`)

O banco local possui os seguintes perfis cadastrados para simulação de fluxo operacional:

| Perfil | Usuário | Função |
| :--- | :--- | :--- |
| **Estoque** | `estoque@tracking.com` | Emite entregas, seleciona tipo de frete e preenche endereço. |
| **Compras** | `compras@tracking.com` | Solicita coletas do Protheus e agenda data/horário e local. |
| **Logística** | `logistica@tracking.com` | Roteiriza entregas/coletas, associa motoristas e gera etiquetas de QR Code. |
| **Motorista** | `motorista@tracking.com` | Visualiza tarefas no celular, simula leitura de QR Code com GPS automático. |
| **Diretoria** | `diretoria@tracking.com` | Visão analítica de KPIs, volumetria e histórico com mapa geográfico. |
| **Administrador** | `admin@tracking.com` | Acesso total e gerenciamento de usuários. |

---

## 📍 Recursos e Recursos Especiais

1. **Auto-preenchimento de Endereço por CEP**: Integração nativa com a API ViaCEP em JavaScript nos formulários de Compras e Estoque.
2. **Transições Inteligentes no Motorista**: O motorista apenas escaneia/informa o QR Code e o sistema automaticamente atualiza o status correto baseado na etapa (`Pendente` $\to$ `Em Transporte` $\to$ `Concluído`) sem necessidade de interação extra do usuário.
3. **Geolocalização Ativa (GPS)**: Utiliza a Geolocation API do navegador do motorista na confirmação do QR Code e salva as coordenadas geográficas na linha do tempo da operação.
4. **Links Diretos p/ Google Maps**: A Diretoria e o Cliente Final conseguem clicar no histórico de status e abrir a rota exata de onde o motorista coletou ou entregou o pedido.
