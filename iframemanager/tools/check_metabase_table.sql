-- =====================================================
-- Consultas SQL para identificar tabela do Metabase
-- =====================================================

-- 1. Verificar se a tabela glpi_plugin_metabase_configs existe
SHOW TABLES LIKE 'glpi_plugin_metabase_configs';
-- Se retornar resultado = existe
-- Se retornar vazio = não existe


-- 2. Buscar todas as tabelas relacionadas ao plugin Metabase
SHOW TABLES LIKE '%metabase%';
-- Mostra todas as tabelas que contém "metabase" no nome


-- 3. Buscar tabelas que começam com glpi_plugin_metabase
SHOW TABLES LIKE 'glpi_plugin_metabase%';


-- 4. Se encontrou a tabela, ver a estrutura dela
DESCRIBE glpi_plugin_metabase_configs;
-- ou
SHOW COLUMNS FROM glpi_plugin_metabase_configs;


-- 5. Ver todos os dados da tabela de configuração do Metabase
SELECT * FROM glpi_plugin_metabase_configs;


-- 6. Buscar especificamente secret_key e metabase_url
SELECT secret_key, metabase_url FROM glpi_plugin_metabase_configs LIMIT 1;


-- 7. Alternativa: Buscar nas configurações do GLPI
SELECT * FROM glpi_configs 
WHERE context LIKE '%metabase%' OR name LIKE '%metabase%';


-- 8. Ver todas as tabelas de plugins instalados
SHOW TABLES LIKE 'glpi_plugin_%';


-- =====================================================
-- Exemplo de uso sequencial:
-- =====================================================

-- Passo 1: Ver todas as tabelas do Metabase
SHOW TABLES LIKE '%metabase%';

-- Passo 2: Se encontrou, ver a estrutura
-- DESCRIBE nome_da_tabela_encontrada;

-- Passo 3: Ver os dados
-- SELECT * FROM nome_da_tabela_encontrada;
