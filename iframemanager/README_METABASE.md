# 📊 Integração Metabase - GLPI Iframe Manager

## 🎯 Visão Geral

O plugin IframeManager integra-se perfeitamente com o **plugin oficial GLPI Metabase** para exibir dashboards e questions do Metabase com assinatura automática JWT.

**Características:**
- ✅ Geração automática de token JWT e assinatura de URL
- ✅ Usa a configuração do plugin GLPI Metabase (gerenciamento centralizado de token)
- ✅ Não é necessário armazenar tokens por iframe (seguro)
- ✅ Parâmetros de contexto do usuário incluídos automaticamente
- ✅ Suporte para dashboards e questions
- ✅ Expiração de token (10 minutos por padrão)

---

## 📋 Pré-requisitos

### 1. Instalar o Plugin GLPI Metabase

Primeiro, você precisa ter o plugin oficial GLPI Metabase instalado e configurado:

1. Baixe o plugin Metabase do marketplace GLPI ou GitHub
2. Instale no diretório `plugins/metabase/`
3. Ative o plugin em **Configurar > Plugins > Metabase**
4. Configure as configurações de conexão do Metabase

### 2. Configurar o Token Secreto do Metabase

No plugin Metabase do GLPI:

1. Acesse **Configurar > Plugins > Metabase**
2. Digite a URL do seu site Metabase (ex: `http://10.62.150.135:3000`)
3. Digite sua **Chave Secreta do Metabase** (encontrada em Metabase Admin > Settings > Embedding)
4. Salve a configuração

**Importante:** O plugin IframeManager usará automaticamente esta chave secreta da configuração do plugin Metabase.

---

## 🔧 Configuração

### Passo 1: Criar um Iframe no IframeManager

1. Acesse **Ferramentas > Iframes > Manage Iframes**
2. Clique em **Adicionar**
3. Preencha o formulário:
   - **Nome**: ex: "Dashboard de Vendas"
   - **Descrição**: Descrição opcional
   - **Link**: Cole a URL do dashboard ou question do Metabase
   - **Is Metabase Dashboard?**: Selecione **Sim** ✅
   - **Ativo**: Sim

### Passo 2: Formato da URL do Metabase

O plugin suporta dois tipos de URLs do Metabase:

**Dashboard:**
```
http://seu-metabase:3000/dashboard/3
```

**Question:**
```
http://seu-metabase:3000/question/5
```

**Nota:** O plugin detecta automaticamente o tipo (dashboard ou question) a partir da URL.

---

## ⚙️ Como Funciona

### Recuperação Automática de Token

Quando você marca um iframe como "Is Metabase Dashboard = Sim":

1. O plugin verifica se o plugin GLPI Metabase está instalado
2. Recupera o token secreto da configuração do plugin Metabase
3. Analisa a URL do iframe para extrair:
   - URL do site Metabase
   - Tipo de recurso (dashboard ou question)
   - ID do recurso
4. Gera um token JWT assinado com:
   - Parâmetros do usuário (user_id, user_name, user_email, etc.)
   - Tempo de expiração (10 minutos)
   - Permissões do recurso
5. Retorna uma URL totalmente assinada pronta para embedding

### Fluxo de Processamento de URL

```
URL Original: http://10.62.150.135:3000/dashboard/3
         ↓
getProcessedUrl(iframe_id)
         ↓
Verifica flag is_metabase
         ↓
getMetabaseToken() → Busca da configuração do plugin GLPI Metabase
         ↓
generateMetabaseUrl() → Analisa URL, gera JWT
         ↓
MetabaseEmbed::generateDashboardUrl()
         ↓
URL Assinada: http://10.62.150.135:3000/embed/dashboard/eyJhbGc...
```

### Parâmetros do Usuário

O plugin inclui automaticamente o seguinte contexto do usuário na URL assinada:

- `user_id` - ID do usuário GLPI
- `user_name` - Nome completo do usuário
- `user_email` - Endereço de email do usuário
- `user_login` - Nome de login do usuário
- `user_firstname` - Primeiro nome do usuário
- `user_lastname` - Sobrenome do usuário

Esses parâmetros podem ser usados no Metabase para filtrar dados com base no usuário logado no GLPI.

---

## 🖥️ Uso

### Visualizando Iframes do Metabase

Uma vez configurado, você pode visualizar seus dashboards do Metabase de várias maneiras:

**1. Iframe Viewer**
- Acesse **Ferramentas > Iframes > Iframe Viewer**
- Selecione seu iframe Metabase no dropdown
- Clique em "View"
- A URL assinada será gerada automaticamente

**2. Custom Charts**
- Acesse **Ferramentas > Iframes > Custom Charts**
- Selecione seu dashboard Metabase
- Visualize em um layout focado em gráficos

**3. Dashboard**
- Acesse **Ferramentas > Iframes > Dashboard**
- Visualize múltiplos iframes em um layout de grade

---

## 🔒 Segurança

### Gerenciamento de Token

✅ **Seguro:** Tokens são armazenados centralmente na configuração do plugin GLPI Metabase (não por iframe)

✅ **Expiração automática:** Tokens JWT expiram após 10 minutos

✅ **Sem segredos expostos:** Tokens nunca são visíveis na URL do iframe (apenas o JWT assinado)

✅ **Contexto do usuário:** Cada URL assinada inclui as informações do usuário atual

### Boas Práticas

1. **Restringir acesso ao Metabase:** Permita embedding apenas em domínios confiáveis
2. **Use HTTPS:** Sempre use HTTPS para instâncias Metabase de produção
3. **Rotação regular de token:** Altere periodicamente sua chave secreta do Metabase
4. **Verifique permissões:** Certifique-se de que os usuários vejam apenas dados autorizados

---

## 🔧 Solução de Problemas

### Problema: Iframe mostra "Embedding is not enabled"

**Solução:** Habilite embedding no Metabase:
1. Acesse o painel Admin do Metabase
2. Settings > Embedding
3. Ative "Embedding secret key"
4. Copie a chave secreta para a configuração do plugin GLPI Metabase

---

### Problema: Iframe mostra em branco ou "Invalid token"

**Possíveis causas:**
1. ❌ Plugin Metabase não instalado ou configurado
2. ❌ Chave secreta incompatível
3. ❌ Token expirado (atualize a página)

**Soluções:**
1. Verifique se o plugin Metabase está ativo: Configurar > Plugins > Metabase
2. Verifique se a chave secreta na configuração do plugin Metabase corresponde às configurações admin do Metabase
3. Atualize a página do visualizador de iframe para gerar um novo token

---

### Problema: Checkbox "Is Metabase Dashboard?" não salva

**Solução:** 
1. Verifique se a migração do banco de dados foi executada com sucesso
2. Verifique se o campo `is_metabase` existe na tabela `glpi_plugin_iframemanager_iframes`:
   ```sql
   DESCRIBE glpi_plugin_iframemanager_iframes;
   ```
3. Se estiver faltando, reinstale o plugin

---

### Problema: URL original mostrada em vez de URL assinada

**Possíveis causas:**
1. ❌ Plugin GLPI Metabase não configurado com chave secreta
2. ❌ Formato de URL Metabase inválido
3. ❌ Checkbox `is_metabase` não marcado

**Soluções:**
1. Configure a chave secreta no plugin GLPI Metabase
2. Certifique-se de que a URL corresponde ao formato: `http://site:porta/dashboard/ID` ou `http://site:porta/question/ID`
3. Edite o iframe e marque "Is Metabase Dashboard? = Sim"

---

### Problema: Parâmetros do usuário não funcionam nos filtros do Metabase

**Solução:**
1. No Metabase, crie um dashboard com um parâmetro (ex: `user_id`)
2. Edite as configurações do dashboard
3. Mapeie o parâmetro para o contexto do usuário do payload JWT
4. O plugin IframeManager envia automaticamente todos os parâmetros do usuário no JWT

---

## 📝 Exemplos

### Exemplo 1: Dashboard Básico

**URL do Metabase:**
```
http://10.62.150.135:3000/dashboard/3
```

**Configuração:**
- Nome: "Visão Geral de Vendas"
- Link: `http://10.62.150.135:3000/dashboard/3`
- Is Metabase Dashboard?: **Sim**

**Resultado:**
```
http://10.62.150.135:3000/embed/dashboard/eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

---

### Exemplo 2: Question com Filtro de Usuário

**URL do Metabase:**
```
http://10.62.150.135:3000/question/5
```

**Configuração do Metabase:**
1. Crie uma question com SQL: `SELECT * FROM tickets WHERE user_id = {{user_id}}`
2. Adicione um parâmetro `user_id` mapeado para o payload JWT

**Configuração do IframeManager:**
- Nome: "Meus Tickets"
- Link: `http://10.62.150.135:3000/question/5`
- Is Metabase Dashboard?: **Sim**

**Resultado:** Cada usuário vê apenas seus próprios tickets com base em seu `user_id` do GLPI

---

## ✅ Checklist de Integração

- [ ] Plugin GLPI Metabase instalado
- [ ] Plugin GLPI Metabase configurado com chave secreta
- [ ] Embedding do Metabase habilitado (Admin > Settings > Embedding)
- [ ] Chave secreta corresponde entre GLPI e Metabase
- [ ] Iframe criado com URL válida do Metabase
- [ ] Checkbox "Is Metabase Dashboard?" está **Sim**
- [ ] Iframe está ativo
- [ ] Usuário tem permissão para visualizar iframes

---

## 🔌 Integração com Plugin GLPI Metabase

### Como o Token é Recuperado

O plugin IframeManager busca o token secreto do Metabase usando a seguinte ordem de prioridade:

**1. Tabela do Plugin Metabase:**
```sql
SELECT secret_key FROM glpi_plugin_metabase_configs LIMIT 1
```

**2. Configurações GLPI (fallback):**
```php
$config = Config::getConfigurationValues('plugin:Metabase');
$token = $config['secret_key'] ?? null;
```

### Verificar Configuração do Plugin Metabase

Para verificar se o plugin Metabase está configurado corretamente:

```sql
-- Verificar se a tabela existe
SHOW TABLES LIKE 'glpi_plugin_metabase_configs';

-- Verificar se há configuração
SELECT * FROM glpi_plugin_metabase_configs;
```

---

## 🛠️ Referência da API

### Iframe::getProcessedUrl($id)

Processa uma URL de iframe e retorna a URL assinada se for um iframe Metabase.

**Parâmetros:**
- `$id` (int) - ID do Iframe

**Retorna:**
- (string) - URL Metabase assinada ou URL processada com placeholders substituídos

**Uso:**
```php
$url = Iframe::getProcessedUrl(5);
echo "<iframe src='{$url}' width='100%' height='600'></iframe>";
```

---

### Iframe::getMetabaseToken()

Recupera o token secreto do Metabase da configuração do plugin GLPI Metabase.

**Retorna:**
- (string|null) - Token secreto ou null se não encontrado

**Ordem de Recuperação do Token:**
1. Verifica tabela `glpi_plugin_metabase_configs`
2. Fallback para `Config::getConfigurationValues('plugin:Metabase')`

---

### MetabaseEmbed::generateDashboardUrl()

Gera uma URL de dashboard Metabase assinada com token JWT.

**Parâmetros:**
- `$siteUrl` (string) - URL do site Metabase
- `$secretKey` (string) - Chave secreta do Metabase
- `$dashboardId` (int) - ID do Dashboard
- `$params` (array) - Parâmetros do usuário
- `$expiration` (int) - Expiração do token em minutos (padrão: 10)
- `$bordered` (bool) - Mostrar borda (padrão: true)
- `$titled` (bool) - Mostrar título (padrão: true)

**Retorna:**
- (string) - URL assinada pronta para embedding em iframe

---

## 📚 Recursos Adicionais

- [Documentação do Plugin GLPI Metabase](https://github.com/pluginsGLPI/metabase)
- [Documentação de Embedding do Metabase](https://www.metabase.com/docs/latest/embedding/introduction)
- [Especificação de Token JWT](https://jwt.io/)

---

## 💬 Suporte

Para problemas ou perguntas:
1. Verifique esta documentação
2. Revise os logs do GLPI: `files/_log/php-errors.log`
3. Verifique os logs do Metabase para erros de embedding
4. Verifique se a estrutura da tabela do banco de dados está correta

---

## 🎉 Conclusão

A integração entre IframeManager e o plugin GLPI Metabase oferece uma solução segura e eficiente para embedar dashboards do Metabase diretamente no GLPI. Com o gerenciamento centralizado de tokens e geração automática de JWT, você pode fornecer visualizações personalizadas para cada usuário sem comprometer a segurança.
