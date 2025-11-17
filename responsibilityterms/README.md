# 📋 Responsibility Terms Plugin for GLPI

Sistema para cadastro de templates de termos e geração de termos de responsabilidade para equipamentos vinculados a usuários.

## 📑 Funcionalidades

### 1. **Templates de Termos**
- Crie modelos reutilizáveis para diferentes tipos de termos
- Configure quais tipos de equipamentos incluir:
  - 💻 Computadores
  - 📱 Telefones
  - 📞 Linhas (CHIPs)
- Utilize placeholders dinâmicos:
  - `{USER_NAME}` - Nome completo do usuário
  - `{USER_EMAIL}` - Email do usuário
  - `{USER_REGISTRATION}` - Matrícula
  - `{EQUIPMENT_LIST}` - Lista de equipamentos vinculados
  - `{DATE}` - Data atual

### 2. **Geração de Termos**
- Gere PDFs personalizados para usuários específicos
- Vincule automaticamente os equipamentos do usuário
- Um usuário pode ter múltiplos termos (computadores, telefones, etc.)
- PDFs armazenados como BLOB no banco de dados
- Visualize ou baixe os PDFs gerados

### 3. **Integração com Assinatura Digital**
- Configure API de assinatura digital
- Suporte para autenticação Basic ou Bearer
- Envie termos diretamente para plataforma de assinatura
- Acompanhe status: Pendente, Enviado, Assinado, Rejeitado

## 🚀 Instalação

### Requisitos
- GLPI 10.0.0 ou superior
- PHP 7.4 ou superior
- Extensão PHP GD (para geração de PDFs)

### Passos

1. Extraia o plugin no diretório de plugins do GLPI:
```bash
cd /var/www/html/glpi/plugins
unzip responsibilityterms.zip
```

2. Acesse GLPI como administrador

3. Vá em **Configurar → Plugins**

4. Clique em **Instalar** e depois **Ativar** o plugin "Responsibility Terms"

5. Configure as permissões para perfis desejados

## 📖 Utilização

### Criar Template

1. Acesse **Ferramentas → Termos → Templates de Termos**
2. Clique em **Adicionar**
3. Preencha:
   - Nome do template
   - Conteúdo (use placeholders)
   - Selecione tipos de equipamentos
4. Salve

**Exemplo de Template:**
```
TERMO DE RESPONSABILIDADE DE EQUIPAMENTOS

Eu, {USER_NAME}, matrícula {USER_REGISTRATION}, declaro ter recebido os seguintes equipamentos:

{EQUIPMENT_LIST}

Me comprometo a zelar pelos equipamentos e devolvê-los quando solicitado.

Data: {DATE}
```

### Gerar Termo para Usuário

1. Acesse **Administração → Usuários**
2. Clique no usuário desejado
3. Vá na aba **Termos**
4. Selecione um template
5. Clique em **Gerar PDF**

### Configurar Assinatura Digital

1. Acesse **Ferramentas → Termos → Configurações**
2. Preencha:
   - URL da API de assinatura
   - Método HTTP (geralmente POST)
   - Tipo de autenticação (Basic ou Bearer)
   - Credenciais correspondentes
3. Salve

**Exemplo de Payload Enviado:**
```json
{
  "document": "<base64_do_pdf>",
  "user_id": 123,
  "term_id": 456,
  "filename": "termo_joao_silva_2025-11-17_14-30-00.pdf"
}
```

## 🗂️ Estrutura de Menus

```
📁 Ferramentas
  └─ 📁 Termos
      ├─ 📄 Templates de Termos ─► Criar/editar modelos
      └─ ⚙️ Configurações ────────► Configurar assinatura digital

📁 Administração → Usuários
  └─ 📝 Aba "Termos" ─────────────► Gerar termos para usuário específico
```

## 🗄️ Estrutura de Banco de Dados

### Tabelas Criadas

- `glpi_plugin_responsibilityterms_templates` - Templates de termos
- `glpi_plugin_responsibilityterms_terms` - Termos gerados (com PDFs)
- `glpi_plugin_responsibilityterms_items` - Vinculação equipamento ↔ termo
- `glpi_plugin_responsibilityterms_configs` - Configurações de assinatura

## 🔐 Permissões

O plugin cria dois conjuntos de permissões:

- **plugin_responsibilityterms_template** - Gerenciar templates
- **plugin_responsibilityterms_term** - Gerar e visualizar termos

Configure em **Administração → Perfis → [Nome do Perfil] → Responsibility Terms**

## 🛠️ Desenvolvimento

### Estrutura de Arquivos
```
responsibilityterms/
├── front/              # Páginas PHP
│   ├── termtemplate.php
│   ├── termtemplate.form.php
│   ├── term.form.php
│   └── config.form.php
├── src/                # Classes
│   ├── TermTemplate.php
│   ├── Term.php
│   ├── Config.php
│   ├── TermsMenu.php
│   └── Profile.php
├── locales/            # Traduções
├── setup.php           # Inicialização
├── hook.php            # Instalação/desinstalação
└── responsibilityterms.xml  # Metadados
```

### Contribuindo

1. Fork o repositório
2. Crie uma branch para sua feature (`git checkout -b feature/nova-funcionalidade`)
3. Commit suas mudanças (`git commit -am 'Adiciona nova funcionalidade'`)
4. Push para a branch (`git push origin feature/nova-funcionalidade`)
5. Abra um Pull Request

## 📝 Licença

Este plugin é distribuído sob a licença GPL v2+.

## 🤝 Suporte

- **Issues:** https://github.com/f13-tecnologia/responsibilityterms/issues
- **Documentação:** Veja [Termo de responsabilidade.md](../../../Termo%20de%20responsabilidade.md)

## 👥 Autores

Desenvolvido por **F13 Tecnologia**

---

## 🔮 Roadmap

- [ ] Geração de PDF com TCPDF
- [ ] Templates visuais (editor WYSIWYG)
- [ ] Assinatura eletrônica integrada
- [ ] Histórico de revisões de termos
- [ ] Notificações por email
- [ ] Relatórios de termos pendentes
- [ ] Exportação em lote
- [ ] Suporte a múltiplos idiomas em templates
