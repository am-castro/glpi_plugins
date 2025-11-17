# 🚫 Solução: "This content is blocked" no Metabase

## ⚡ Solução Rápida (SEM Plugin)

Como você não tem o plugin GLPI Metabase instalado, use **Public Sharing**:

### 1️⃣ Habilitar Embedding Global no Metabase

1. Acesse: `http://10.62.150.135:3000`
2. Faça login como **Admin**
3. Clique no ícone **⚙️ (Settings)** → **Admin settings**
4. Menu lateral: **Embedding**
5. ✅ **Enable embedding**
6. Copie a **Embedding secret key** (guarde para depois)

### 2️⃣ Habilitar Embedding no Dashboard Específico

1. Abra o dashboard: `http://10.62.150.135:3000/dashboard/3`
2. Clique no ícone **🔗 (Share)** no canto superior direito
3. Vá na aba **"Embedding"**
4. ✅ **Enable sharing**
5. Configure os parâmetros (deixe todos como "Disabled" por enquanto)
6. Clique em **Save**

### 3️⃣ Usar URL Pública (Alternativa Temporária)

Se o embedding ainda não funcionar, use o Public Link:

1. No dashboard, clique em **🔗 Share**
2. Vá na aba **"Public link"**
3. ✅ **Enable sharing**
4. **Copie a URL pública** (parece com: `http://10.62.150.135:3000/public/dashboard/abc123...`)

### 4️⃣ Atualizar o Iframe no GLPI

**Opção A - Se habilitou Embedding:**
```sql
# Mantenha is_metabase = 1 e use a URL normal
UPDATE glpi_plugin_iframemanager_iframes 
SET is_metabase = 1, 
    link = 'http://10.62.150.135:3000/dashboard/3'
WHERE id = 2;
```

**Opção B - Se vai usar Public Link:**
```sql
# Desmarque is_metabase e use a URL pública
UPDATE glpi_plugin_iframemanager_iframes 
SET is_metabase = 0, 
    link = 'http://10.62.150.135:3000/public/dashboard/abc123...'
WHERE id = 2;
```

---

## 🔐 Solução Completa (COM Plugin JWT)

Se quiser usar JWT signing (mais seguro):

### 1️⃣ Instalar Plugin GLPI Metabase

```bash
cd /home/marcos/Documentos/projects/PHP/glpi_cru/plugins
git clone https://github.com/pluginsGLPI/metabase.git
```

### 2️⃣ Ativar no GLPI

1. `http://localhost:8080` → **Configurar** → **Plugins**
2. Procure **"Metabase"**
3. Clique em **Instalar** → **Ativar**

### 3️⃣ Configurar Secret Key

1. No GLPI: **Configurar** → **Plugins** → **Metabase**
2. Cole a **Embedding secret key** (copiada do Metabase)
3. Salve

### 4️⃣ Testar

```
http://localhost:8080/plugins/iframemanager/front/iframe.debug.php?id=2
```

Agora deve aparecer:
- ✅ Token Metabase configurado
- ✅ URL assinada com JWT

---

## 📊 Comparação dos Métodos

| Método | Segurança | Complexidade | Funcionalidade |
|--------|-----------|--------------|----------------|
| **Public Link** | ⚠️ Baixa | ✅ Simples | Básica |
| **Embedding (sem JWT)** | ⚠️ Média | ✅ Simples | Boa |
| **JWT Signing** | ✅ Alta | ⚠️ Requer plugin | Completa |

---

## 🔍 Verificar Configuração do Metabase

### Comando para testar se embedding está habilitado:

```bash
curl -s http://10.62.150.135:3000/api/session/properties | grep -i embedding
```

Se retornar `"enable-embedding":true`, está habilitado.

---

## ⚙️ Configurações de Segurança do Metabase

Se continuar bloqueado, verifique estas configurações no Metabase:

### 1. Embedding Allowed Origins

No Metabase Admin → Settings → Embedding:

- **Allowed Origins**: Adicione `http://localhost:8080` (ou seu domínio GLPI)

### 2. X-Frame-Options

Verifique as configurações do servidor Metabase. Se estiver usando Docker/nginx, pode ter headers bloqueando iframes.

### 3. Content Security Policy

Alguns servidores adicionam headers CSP que bloqueiam iframes. Verifique nos headers HTTP do Metabase.

---

## 🧪 Teste Rápido

Para verificar se o problema é de embedding ou conexão:

```bash
# Teste 1: Acesso direto
curl -I http://10.62.150.135:3000/dashboard/3

# Teste 2: Verificar headers
curl -I http://10.62.150.135:3000/dashboard/3 | grep -i frame
```

Se aparecer `X-Frame-Options: DENY` ou `SAMEORIGIN`, o Metabase está bloqueando iframes externos.

---

## 📝 Comandos SQL Úteis

```sql
-- Ver configuração atual do iframe
SELECT id, name, link, is_metabase FROM glpi_plugin_iframemanager_iframes WHERE id = 2;

-- Testar com URL pública (is_metabase = 0)
UPDATE glpi_plugin_iframemanager_iframes 
SET is_metabase = 0, 
    link = 'http://10.62.150.135:3000/public/dashboard/SEU-HASH-AQUI'
WHERE id = 2;

-- Voltar para modo Metabase (is_metabase = 1)
UPDATE glpi_plugin_iframemanager_iframes 
SET is_metabase = 1, 
    link = 'http://10.62.150.135:3000/dashboard/3'
WHERE id = 2;
```

---

## 💡 Recomendação

Para começar **rapidamente**:
1. ✅ Habilite **Public Sharing** no dashboard
2. ✅ Use **is_metabase = 0**
3. ✅ Cole a **URL pública**

Depois, quando tiver tempo:
1. 🔧 Instale o plugin GLPI Metabase
2. 🔧 Configure JWT signing
3. 🔧 Use **is_metabase = 1** com segurança

---

## 🆘 Se nada funcionar

Verifique os logs do navegador (F12 → Console) para ver o erro exato:

- ❌ `X-Frame-Options denied` → Problema de headers do Metabase
- ❌ `CORS error` → Problema de CORS
- ❌ `401 Unauthorized` → Problema de autenticação/token
- ❌ `This content is blocked` → Embedding não habilitado no dashboard

Cada erro tem uma solução específica. Me avise qual erro aparece!
