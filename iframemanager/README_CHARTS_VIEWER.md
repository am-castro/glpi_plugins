# 📊 Visualizador de Gráficos (Charts Viewer)

## 🎯 Nova Funcionalidade Adicionada

Criada uma nova tela de visualização de iframes com seleção dinâmica, acessível através do menu **Ativos > Gráficos**.

## 📍 Localização no Menu

```
Menu Principal
└── Ativos (Assets)
    ├── Computadores
    ├── Monitores
    ├── ...
    ├── 📊 Gráficos  ← NOVO!
    └── Dashboard
```

## 🎨 Funcionalidades

### 1. **Seleção Dinâmica de Iframes**
- Dropdown com lista de todos os iframes ativos
- Seleção automática do primeiro iframe ao carregar a página
- Atualização instantânea ao selecionar outro iframe

### 2. **Visualização do Iframe**
- Iframe em tela cheia (85vh)
- Substituição automática de placeholders do usuário
- Design responsivo com Bootstrap

### 3. **Ações Disponíveis**
- **View** → Recarrega com o iframe selecionado
- **Edit** → Abre o formulário de edição do iframe
- **Open in new window** → Abre o iframe em nova janela

## 📝 Arquivo Criado

```
plugins/example/front/iframe.viewer.php
```

### Estrutura da Página

```php
┌─────────────────────────────────────────┐
│  🔽 Select a chart to view              │
│  ┌──────────────────────┐               │
│  │ [Dropdown de Iframes]│  [View] [Edit]│
│  └──────────────────────┘               │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  Nome do Iframe       [Open in new ↗]  │
├─────────────────────────────────────────┤
│                                         │
│         [IFRAME RENDERIZADO]            │
│         (85vh altura)                   │
│                                         │
└─────────────────────────────────────────┘
```

## 🔧 Configuração no Menu

### Alteração no `setup.php`

```php
// Adiciona Charts ao menu Assets
$PLUGIN_HOOKS['menu_toadd']['example'] = [
    'plugins' => Example::class,
    'tools'   => Example::class,
    'assets'  => 'PluginExampleCharts'  // ← NOVO!
];

// Configuração do submenu Charts
$PLUGIN_HOOKS['submenu_entry']['example']['assets']['PluginExampleCharts'] = [
    'title' => __('Charts', 'example'),
    'page'  => '/plugins/example/front/iframe.viewer.php',
    'icon'  => 'ti ti-chart-bar',
];
```

## 🎯 Fluxo de Uso

### Cenário 1: Visualizar Gráficos

```
1. Usuário acessa: Ativos > Gráficos
2. Sistema carrega lista de iframes ativos
3. Primeiro iframe é selecionado automaticamente
4. Iframe é renderizado com dados do usuário
```

### Cenário 2: Trocar de Gráfico

```
1. Usuário seleciona outro iframe no dropdown
2. Formulário é submetido automaticamente (onchange)
3. Página recarrega com novo iframe
4. Placeholders são substituídos
```

### Cenário 3: Editar Iframe

```
1. Usuário clica em "Edit"
2. Abre iframe.form.php com ID do iframe
3. Após salvar, redireciona para iframe.display.php
```

## 🔐 Placeholders Suportados

Os mesmos placeholders da visualização individual:

| Placeholder | Substituído por |
|------------|-----------------|
| `{user_id}` | ID do usuário |
| `{user_name}` | Nome de usuário (login) |
| `{user_realname}` | Sobrenome |
| `{user_firstname}` | Nome |
| `{user_email}` | Email |
| `{user_login}` | Login |

### Exemplo de URL

```
https://dashboard.example.com/chart?user={user_id}&email={user_email}
```

Será transformado em:
```
https://dashboard.example.com/chart?user=42&email=joao.silva@empresa.com
```

## 🎨 Interface

### Quando NÃO há iframes cadastrados

```html
┌─────────────────────────────────────────┐
│  ⚠️ No active iframes found.            │
│     Please create and activate iframes  │
│     first.                              │
│                                         │
│  [Create iframe]                        │
└─────────────────────────────────────────┘
```

### Quando há iframes cadastrados

```html
┌─────────────────────────────────────────┐
│  Select chart:                          │
│  ┌────────────────────────────────────┐ │
│  │ Dashboard PowerBI - Vendas 2024   ↓│ │
│  ├────────────────────────────────────┤ │
│  │ Dashboard PowerBI                   │ │
│  │ Relatório Financeiro                │ │
│  │ Gráfico de Performance              │ │
│  └────────────────────────────────────┘ │
│  [View]  [Edit]                         │
└─────────────────────────────────────────┘
```

## 🔒 Segurança

### Validações Implementadas

1. ✅ **Autenticação**: `Session::checkLoginUser()`
2. ✅ **Apenas iframes ativos**: `is_active = 1`
3. ✅ **Validação de URL**: Apenas `http://` e `https://`
4. ✅ **Escape de HTML**: `htmlspecialchars()`
5. ✅ **Validação de ID**: Cast para `(int)`

## 📊 Casos de Uso

### 1. Dashboard PowerBI
```
Nome: Dashboard PowerBI Vendas
URL: https://app.powerbi.com/view?r=TOKEN&user={user_email}
Descrição: Dashboard de vendas em tempo real
```

### 2. Grafana Dashboard
```
Nome: Grafana Monitoramento
URL: https://grafana.example.com/d/dashboard?user={user_id}
Descrição: Monitoramento de servidores
```

### 3. Metabase Reports
```
Nome: Metabase Relatórios
URL: https://metabase.example.com/public/dashboard/uuid?user={user_name}
Descrição: Relatórios gerenciais
```

## 🚀 Como Testar

### 1. Criar Iframes de Teste

```
Setup > Plugins > Example > Configuration
ou
Acesse diretamente: /plugins/example/front/iframe.list.php

Criar 3 iframes:
1. "Dashboard 1" - http://example.com/1
2. "Dashboard 2" - http://example.com/2
3. "Dashboard 3" - http://example.com/3

Todos com is_active = 1
```

### 2. Acessar Visualizador

```
Ativos > Gráficos
ou
/plugins/example/front/iframe.viewer.php
```

### 3. Testar Funcionalidades

- ✅ Seleção de diferentes iframes no dropdown
- ✅ Botão "View" recarrega a página
- ✅ Botão "Edit" abre o formulário
- ✅ Botão "Open in new window" abre em nova aba
- ✅ Placeholders são substituídos corretamente

## 🎯 Benefícios

### Para Usuários
- ✅ Acesso rápido a múltiplos dashboards
- ✅ Sem necessidade de decorar URLs
- ✅ Interface unificada no GLPI
- ✅ Troca rápida entre gráficos

### Para Administradores
- ✅ Gerenciamento centralizado de dashboards
- ✅ Controle de acesso via permissões GLPI
- ✅ Logs de acesso integrados
- ✅ Fácil manutenção

## 📁 Estrutura de Arquivos

```
plugins/example/
├── front/
│   ├── iframe.list.php          # Lista de iframes
│   ├── iframe.form.php          # Formulário criar/editar
│   ├── iframe.display.php       # Visualização individual
│   └── iframe.viewer.php        # 🆕 Visualizador com seleção
├── src/
│   └── Iframe.php               # Classe principal
└── setup.php                    # 🔄 Modificado (menu)
```

## 🔄 Diferenças entre Páginas

| Página | Propósito | URL com ID? | Dropdown? |
|--------|-----------|-------------|-----------|
| `iframe.list.php` | Listar todos | ❌ | ❌ |
| `iframe.form.php` | Criar/Editar | ✅ | ❌ |
| `iframe.display.php` | Visualizar um | ✅ | ❌ |
| `iframe.viewer.php` | **Visualizar com seleção** | ✅ | ✅ |

## ✨ Próximas Melhorias (Opcional)

- [ ] Adicionar favoritos de iframes por usuário
- [ ] Histórico de iframes visualizados
- [ ] Busca/filtro no dropdown
- [ ] Agrupamento de iframes por categoria
- [ ] Modo fullscreen
- [ ] Atualização automática do iframe

---

**Status**: ✅ Implementado e funcional
**Versão**: 1.0
**Data**: 22 de outubro de 2025
