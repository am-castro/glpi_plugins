-- =====================================================
-- Verificar configuração do Metabase em glpi_configs
-- =====================================================

-- 1. Buscar todas as configurações relacionadas ao Metabase
SELECT * FROM glpi_configs 
WHERE context LIKE '%metabase%' OR context LIKE '%Metabase%';

-- 2. Buscar especificamente secret_key
SELECT * FROM glpi_configs 
WHERE name = 'secret_key' OR name LIKE '%secret%';

-- 3. Buscar metabase_url
SELECT * FROM glpi_configs 
WHERE name = 'metabase_url' OR name LIKE '%metabase%url%';

-- 4. Ver todas as configurações de plugins
SELECT * FROM glpi_configs 
WHERE context LIKE 'plugin:%'
ORDER BY context, name;
