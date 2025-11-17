# 📋 Plugin de Termo de Responsabilidade

Sistema para cadastro de [templates de termos](#o-que-são-templates-de-termos) e geração de [termos de responsabilidade](#o-que-são-termos-de-responsabilidade) para equipamentos vinculados a usuários.

---

## 📑 Índice

1. [Estrutura de Menus](#estrutura-de-menus)
2. [Cadastro de Templates](#1-cadastro-de-templates)
3. [Geração de Termos para Usuários](#2-geração-de-termos-para-usuários)
4. [Configurações de Assinatura Digital](#3-configurações-de-assinatura-digital)
5. [Glossário](#glossário)

---

## 🗂️ Estrutura de Menus

### Menu Principal: Ferramentas → Termos

```
📁 Ferramentas
  └─ 📁 Termos
      ├─ 📄 Template de Termos ──────────► [Criar/editar modelos de termos]
      └─ ⚙️ Configurações ────────────────► [Configurar assinatura digital]

📁 Usuários
  └─ 📝 Termos ──────────────────────────► [Gerar termos para usuários específicos]
```

[🔗 O que são templates?](#o-que-são-templates-de-termos) | [🔗 Como funciona a assinatura digital?](#assinatura-digital)

---

## 1️⃣ Cadastro de Templates

**Caminho:** `Ferramentas → Termos → Template de Termos`

### Funcionalidade
Criação de modelos de termos que serão preenchidos com dados de usuários e equipamentos.

### Vinculação de Equipamentos
O template permite selecionar **múltiplos tipos de equipamentos** que serão vinculados ao [termo](#o-que-são-termos-de-responsabilidade):

| Tipo de Equipamento | Descrição |
|-------------------|-----------|
| 💻 **Computadores** | Desktops, notebooks, workstations |
| 📱 **Telefones** | Celulares corporativos, smartphones |
| 📞 **CHIPs e Linhas** | Cartões SIM, linhas telefônicas |

> **💡 Nota:** Os equipamentos são vinculados automaticamente ao ID do usuário selecionado na etapa de [geração do termo](#2-geração-de-termos-para-usuários).

[🔗 Como criar um template?](#exemplo-de-template) | [🔗 Voltar ao índice](#índice)

---

## 2️⃣ Geração de Termos para Usuários

**Caminho:** `Usuários → Termos`

### Funcionalidade
Gera [PDFs de termos de responsabilidade](#formato-do-pdf) vinculados a usuários específicos.

### Características

#### 📌 Múltiplos Termos por Usuário
Um mesmo usuário pode ter **vários termos**, por exemplo:
- ✅ Termo para computadores
- ✅ Termo para telefones e linhas
- ✅ Termo para outros equipamentos

#### 💾 Armazenamento
- O PDF gerado é salvo no **banco de dados como binário** ([BLOB](https://pt.wikipedia.org/wiki/Objeto_bin%C3%A1rio_grande))
- Pode ser **visualizado** diretamente no sistema
- Pode ser **baixado** caso a visualização não seja suportada

#### ✍️ Assinatura Digital
Após a geração do PDF, o documento pode ser enviado para [assinatura digital](#assinatura-digital) através da URL configurada em `Configurações`.

**Regra de Negócio:**
```
SE configuração de assinatura NÃO existe
  ENTÃO botão "Enviar para Assinatura" fica DESABILITADO
       E exibe AVISO: "Configure o link de assinatura em Ferramentas → Termos → Configurações"
SENÃO
  botão fica HABILITADO
```

[🔗 Como configurar assinatura digital?](#3-configurações-de-assinatura-digital) | [🔗 Voltar ao índice](#índice)

---

## 3️⃣ Configurações de Assinatura Digital

**Caminho:** `Ferramentas → Termos → Configurações`

### Parâmetros de Integração

| Campo | Tipo | Descrição | Obrigatório |
|-------|------|-----------|-------------|
| **URL** | Text | Endpoint da API de assinatura | ✅ Sim |
| **Método** | Select | Método HTTP (fixo: `POST`) | ✅ Sim |
| **Tipo de Autenticação** | Radio | `BASIC` ou `Bearer` | ✅ Sim |
| **Usuário** | Text | Username para [Basic Auth](https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Authentication#autenticação_basic) | ⚠️ Se Basic |
| **Senha** | Password | Password para Basic Auth | ⚠️ Se Basic |
| **Token** | Text | Token para [Bearer Auth](https://developer.mozilla.org/pt-BR/docs/Web/HTTP/Authentication#esquema_de_autenticação_bearer) | ⚠️ Se Bearer |

### Exemplo de Configuração

#### Autenticação Basic
```http
POST https://api.assinatura.com/v1/documentos
Authorization: Basic dXNlcjpwYXNz
Content-Type: application/json

{
  "documento": "<base64_pdf>",
  "usuario_id": "12345"
}
```

#### Autenticação Bearer
```http
POST https://api.assinatura.com/v1/documentos
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
Content-Type: application/json

{
  "documento": "<base64_pdf>",
  "usuario_id": "12345"
}
```

> **🔮 Futuro:** Novas configurações poderão ser adicionadas nesta seção.

[🔗 Voltar ao índice](#índice)

---

## 📖 Glossário

### O que são Templates de Termos?
Modelos de documentos pré-formatados com campos dinâmicos que serão preenchidos automaticamente com dados de usuários e equipamentos. Similar a um [template de e-mail](https://pt.wikipedia.org/wiki/Sistema_de_templates).

### O que são Termos de Responsabilidade?
Documentos legais que estabelecem as responsabilidades do colaborador sobre equipamentos corporativos recebidos. [Saiba mais](https://www.jusbrasil.com.br/artigos/termo-de-responsabilidade-de-equipamentos/1255832735).

### Formato do PDF
Documento em [Portable Document Format](https://pt.wikipedia.org/wiki/PDF), formato universal para visualização de documentos.

### Assinatura Digital
Processo eletrônico que garante autenticidade e integridade de documentos digitais. [Entenda a diferença entre assinatura digital e eletrônica](https://www.gov.br/iti/pt-br/assuntos/noticias/indice-de-noticias/assinatura-eletronica-e-assinatura-digital-entenda-as-diferencas).

### BLOB (Binary Large Object)
Tipo de dado usado para armazenar arquivos binários (como PDFs, imagens) diretamente no banco de dados. [Referência técnica](https://developer.mozilla.org/pt-BR/docs/Web/API/Blob).

---

## 🎯 Fluxo Completo de Uso

```mermaid
graph TD
    A[1. Criar Template] -->|Ferramentas → Termos → Templates| B[Definir equipamentos]
    B --> C[2. Configurar Assinatura]
    C -->|Ferramentas → Termos → Configurações| D[Inserir URL e credenciais]
    D --> E[3. Gerar Termo]
    E -->|Usuários → Termos| F[Selecionar usuário]
    F --> G[PDF gerado e salvo]
    G --> H{Enviar para assinatura?}
    H -->|Sim| I[POST para API configurada]
    H -->|Não| J[Apenas download/visualização]
```

[🔗 Voltar ao topo](#-plugin-de-termo-de-responsabilidade)