# 🚀 Guia Rápido de Instalação - Responsibility Terms

## Instalação no GLPI

### Passo 1: Copiar o Plugin

```bash
# Navegue até o diretório de plugins do GLPI
cd /var/www/html/glpi/plugins

# Copie o plugin
cp -r /home/marcos/Documentos/projects/PHP/glpi_cru/plugins/responsibilityterms .

# Ajuste as permissões
chown -R www-data:www-data responsibilityterms
chmod -R 755 responsibilityterms
```

### Passo 2: Instalar via Interface

1. Acesse o GLPI como **Super-Admin**
2. Vá em **Configurar → Plugins**
3. Localize "Responsibility Terms"
4. Clique em **Instalar**
5. Após instalação, clique em **Ativar**

### Passo 3: Verificar Permissões

1. Vá em **Administração → Perfis**
2. Selecione o perfil desejado (ex: Super-Admin, Admin)
3. Vá na aba **Responsibility Terms**
4. Marque as permissões adequadas:
   - ✅ Ler
   - ✅ Criar
   - ✅ Atualizar
   - ✅ Deletar
5. Salve

## Primeiro Uso

### 1. Criar um Template

**Caminho:** Ferramentas → Termos → Templates de Termos

```
Nome: Termo de Computador
Ativo: Sim

Conteúdo:
TERMO DE RESPONSABILIDADE DE EQUIPAMENTO

Eu, {USER_NAME}, matrícula {USER_REGISTRATION}, email {USER_EMAIL},
declaro ter recebido o(s) seguinte(s) equipamento(s):

{EQUIPMENT_LIST}

Comprometo-me a:
- Usar o equipamento apenas para fins profissionais
- Zelar pela conservação e segurança
- Devolver quando solicitado pela empresa

Local e Data: Salvador, {DATE}

_____________________________
Assinatura do Colaborador

Incluir Equipamentos:
☑ Computadores
☐ Telefones
☐ Linhas (CHIPs)
```

### 2. Configurar Assinatura Digital (Opcional)

**Caminho:** Ferramentas → Termos → Configurações

```
URL da API: https://api.assinatura.com/v1/documents
Método HTTP: POST
Tipo de Autenticação: Bearer Token
Bearer Token: seu_token_aqui_12345...
```

### 3. Gerar um Termo

1. Vá em **Administração → Usuários**
2. Clique no usuário (ex: João Silva)
3. Vá na aba **Termos**
4. Selecione template: "Termo de Computador"
5. Clique em **Gerar PDF**
6. **Visualizar PDF** ou **Enviar para Assinatura**

## Troubleshooting

### Plugin não aparece no menu

```bash
# Verifique permissões
ls -la /var/www/html/glpi/plugins/responsibilityterms

# Verifique logs do GLPI
tail -f /var/www/html/glpi/files/_log/php-errors.log
tail -f /var/www/html/glpi/files/_log/sql-errors.log
```

### Erro ao instalar

```sql
-- Verificar se as tabelas foram criadas
SHOW TABLES LIKE 'glpi_plugin_responsibilityterms_%';

-- Deve retornar:
-- glpi_plugin_responsibilityterms_configs
-- glpi_plugin_responsibilityterms_items
-- glpi_plugin_responsibilityterms_templates
-- glpi_plugin_responsibilityterms_terms
```

### PDF não gerado

```php
// Verifique extensão GD do PHP
php -m | grep -i gd

// Se não aparecer, instale:
sudo apt-get install php-gd
sudo systemctl restart apache2
```

## Estrutura de Dados

### Placeholders Disponíveis

| Placeholder | Descrição | Exemplo |
|-------------|-----------|---------|
| `{USER_NAME}` | Nome completo | João Silva |
| `{USER_EMAIL}` | Email | joao.silva@empresa.com |
| `{USER_REGISTRATION}` | Matrícula | 12345 |
| `{EQUIPMENT_LIST}` | Lista de equipamentos | - Computer: DELL-001<br>- Phone: iPhone 12 |
| `{DATE}` | Data atual | 17/11/2025 |

### Fluxo de Estados do Termo

```
[pending] ──(Enviar)──► [sent] ──(API)──► [signed]
                                    │
                                    └────► [rejected]
```

## Próximos Passos

1. ✅ **Crie templates** para diferentes tipos de equipamento
2. ✅ **Configure a API** de assinatura (se disponível)
3. ✅ **Gere termos** para usuários existentes
4. ✅ **Monitore status** dos termos enviados

## Suporte

- 📧 Email: suporte@f13tecnologia.com.br
- 🐛 Issues: https://github.com/f13-tecnologia/responsibilityterms/issues
- 📚 Docs: Ver README.md completo

---

**Desenvolvido por F13 Tecnologia** 🚀
