# Plugin Example - Gerenciamento de Iframes

## 📋 Funcionalidades

Este plugin permite gerenciar iframes personalizados no GLPI com suporte a:
- ✅ Listagem com colunas configuráveis
- ✅ Substituição automática de dados do usuário na URL
- ✅ Controle de permissões por perfil
- ✅ Ativação/desativação de iframes
- ✅ Visualização em tela cheia

## 🎯 Campos Disponíveis

Na tabela de listagem, você tem acesso aos seguintes campos:

| Campo | Descrição | Sempre Visível |
|-------|-----------|----------------|
| **Name** | Nome do iframe | ✅ Sim (padrão) |
| **ID** | Identificador único | ✅ Sim (padrão) |
| **Description** | Descrição detalhada | ⚙️ Opcional |
| **URL** | Link do iframe | ⚙️ Opcional |
| **Active** | Status ativo/inativo | ⚙️ Opcional |
| **View** | Botão para visualizar | ⚙️ Opcional |

## 🔧 Como Adicionar/Remover Colunas na Listagem

### Método 1: Interface Gráfica (Recomendado)

1. **Acesse a listagem de iframes**
   - Navegue até: `Plugins > Example > Iframes`

2. **Clique em "Pesquisar" ou no ícone de busca** (⚙️)
   - Isso abrirá as opções de pesquisa avançada

3. **Na seção "Critérios de Pesquisa"**, clique em **"+"** para adicionar colunas
   - Selecione o campo desejado no dropdown:
     - `Description` → Para ver as descrições
     - `URL` → Para ver os links
     - `Active` → Para ver o status
     - `View` → Para ver o botão de visualização

4. **Salve sua visualização personalizada** (opcional)
   - Clique em "Salvar esta pesquisa"
   - Dê um nome (ex: "Iframes Completo")
   - Marque como padrão se desejar

### Método 2: URL Direta

Você pode adicionar colunas diretamente na URL:

```
# Mostrar Name, Description, URL e Active
/plugins/example/front/iframe.list.php?criteria[0][field]=1&criteria[0][searchtype]=contains&criteria[0][value]=

# Adicionar coluna Description (ID=2)
&criteria[1][field]=2

# Adicionar coluna URL (ID=3)
&criteria[2][field]=3

# Adicionar coluna Active (ID=4)
&criteria[3][field]=4
```

### Método 3: Configuração Padrão via Código

Para sempre mostrar determinadas colunas por padrão, você pode modificar o arquivo `iframe.list.php`:

```php
// Exemplo: forçar exibição de colunas específicas
$_GET['criteria'] = [
    ['field' => 2, 'searchtype' => 'contains', 'value' => ''], // Description
    ['field' => 3, 'searchtype' => 'contains', 'value' => ''], // URL
    ['field' => 4, 'searchtype' => 'contains', 'value' => ''], // Active
    ['field' => 5, 'searchtype' => 'contains', 'value' => ''], // View
];
```

## 🔐 Configuração de Permissões

### 1. Instalar/Reinstalar o Plugin

Após modificações no código de permissões:

```
Setup > Plugins > Example > Reinstall
```

### 2. Configurar Perfil

```
Setup > Profiles > [Seu Perfil] > Example plugin
```

Marque as permissões para **Iframes**:
- ☑️ **Read** → Visualizar listagem e detalhes
- ☑️ **Update** → Criar e editar iframes

## 🎨 Placeholders Disponíveis nas URLs

Ao configurar a URL de um iframe, você pode usar os seguintes placeholders que serão automaticamente substituídos pelos dados do usuário logado:

| Placeholder | Substituído por | Exemplo |
|------------|-----------------|---------|
| `{user_id}` | ID do usuário | `42` |
| `{user_name}` | Nome de usuário (login) | `jsilva` |
| `{user_realname}` | Sobrenome | `Silva` |
| `{user_firstname}` | Nome | `João` |
| `{user_email}` | Email padrão | `joao.silva@empresa.com` |
| `{user_login}` | Login | `jsilva` |

### Exemplo de URL com Placeholders

```
https://dashboard.example.com/user?id={user_id}&name={user_name}&email={user_email}
```

Será transformado em:
```
https://dashboard.example.com/user?id=42&name=jsilva&email=joao.silva@empresa.com
```

## 📊 Estrutura da Tabela

```sql
CREATE TABLE `glpi_plugin_example_iframes` (
    `id` int NOT NULL auto_increment,
    `name` varchar(255) default NULL,
    `description` TEXT,
    `link` TEXT NOT NULL,
    `is_active` tinyint NOT NULL default '1',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 🚀 Exemplo de Uso Completo

### 1. Criar um Iframe

```
Plugins > Example > Iframes > Add
```

Preencha:
- **Name**: Dashboard PowerBI
- **Description**: Dashboard de vendas com dados em tempo real
- **URL**: `https://app.powerbi.com/view?r=TOKEN&user={user_email}&name={user_name}`
- **Active**: ✅ Yes

### 2. Personalizar Visualização da Listagem

1. Vá em `Plugins > Example > Iframes`
2. Clique no ícone de busca/filtro
3. Adicione as colunas:
   - Description
   - Active
   - View
4. Salve como "Iframes - Visão Completa"

### 3. Visualizar o Iframe

1. Na listagem, clique no botão **"View"** (👁️)
2. O iframe será aberto com os placeholders substituídos
3. A URL será validada (apenas http/https permitidos)

## 🐛 Troubleshooting

### Problema: Colunas não aparecem

**Solução**: 
- Limpe o cache do GLPI: `php bin/console cache:clear`
- Verifique se o plugin foi reinstalado após alterações

### Problema: Permissão negada ao acessar listagem

**Solução**:
1. Verifique em `Setup > Profiles > [Perfil] > Example plugin`
2. Certifique-se que **Iframes** tem permissão **Read** marcada
3. Reinstale o plugin se necessário

### Problema: Placeholders não são substituídos

**Solução**:
- Verifique se está usando `iframe.display.php` e não `iframe.form.php`
- Confirme que o usuário tem email configurado no perfil
- Use exatamente os nomes dos placeholders listados acima

## 📝 Notas Técnicas

- **GLPI v10/v11**: Totalmente compatível
- **Validação de URL**: Apenas esquemas http/https são permitidos
- **Segurança**: Todas as URLs são escapadas com `htmlspecialchars()`
- **Performance**: Busca otimizada com índices na tabela

## 🔗 Arquivos Importantes

```
plugins/example/
├── front/
│   ├── iframe.list.php          # Listagem de iframes
│   ├── iframe.form.php          # Formulário de edição
│   ├── iframe.display.php       # Visualização do iframe
│   └── iframe.save.php          # Salvar dados
├── src/
│   ├── Iframe.php               # Classe principal
│   └── Profile.php              # Configuração de permissões
└── hook.php                     # Hooks de instalação/desinstalação
```

## ✅ Checklist de Instalação

- [ ] Plugin instalado via `Setup > Plugins > Example > Install`
- [ ] Permissões configuradas em `Setup > Profiles`
- [ ] Tabela `glpi_plugin_example_iframes` criada no banco
- [ ] Iframes de teste criados
- [ ] Colunas personalizadas adicionadas na listagem
- [ ] Visualização testada com placeholders
