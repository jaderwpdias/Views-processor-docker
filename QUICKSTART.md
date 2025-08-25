# 🚀 Guia de Início Rápido - IoT Dashboard

## ⚡ Início Super Rápido (3 passos)

### 1. Clone e Entre no Projeto
```bash
git clone <seu-repositorio>
cd iot-dashboard
```

### 2. Execute o Script de Inicialização
```bash
./start.sh
```

### 3. Acesse o Dashboard
Abra seu navegador e vá para: **http://localhost:8080**

## 🎯 O que você verá

- **Dashboard em tempo real** com métricas do seu computador
- **Gráficos interativos** de CPU, memória e disco
- **Status do sistema** com indicadores visuais
- **API REST** para integração com outros sistemas

## 📊 Métricas Monitoradas

| Métrica | Descrição | Atualização |
|---------|-----------|-------------|
| CPU | Uso percentual e temperatura | A cada 5s |
| Memória | Total, usada e disponível | A cada 5s |
| Disco | Espaço total e usado | A cada 5s |
| Rede | Tráfego de entrada/saída | A cada 5s |
| Sistema | Uptime e load average | A cada 5s |

## 🔧 Comandos Úteis

```bash
# Ver status dos serviços
./start.sh status

# Ver logs em tempo real
./start.sh logs

# Parar todos os serviços
./start.sh stop

# Reiniciar serviços
./start.sh restart
```

## 🚨 Solução de Problemas

### Dashboard não carrega?
```bash
# Verificar se os containers estão rodando
docker-compose ps

# Ver logs do dashboard
docker-compose logs iot-dashboard

# Executar diagnóstico completo
./start.sh test
```

### Diagnóstico Web
Acesse http://localhost:8080/diagnose.php para um diagnóstico completo do sistema.

### Métricas não aparecem?
```bash
# Ver logs do coletor
docker-compose logs iot-collector

# Verificar banco de dados
docker-compose exec iot-db mysql -u iot_user -p iot_metrics
```

### Erro de permissão?
```bash
# Corrigir permissões
sudo chown -R $USER:$USER .
chmod +x start.sh
```

## 📡 API Endpoints

Teste a API diretamente no navegador:

- **Últimas métricas**: http://localhost:8080/api/metrics.php?action=latest
- **Histórico**: http://localhost:8080/api/metrics.php?action=history
- **Dados do gráfico**: http://localhost:8080/api/metrics.php?action=chart

## 🎨 Personalização

### Alterar intervalo de coleta
Edite `collector/src/collector.php` e mude o valor de `sleep(5)` para o intervalo desejado.

### Modificar thresholds de alerta
```sql
-- Conectar ao banco
docker-compose exec iot-db mysql -u iot_user -p iot_metrics

-- Alterar limite de CPU
UPDATE system_config SET config_value = '70' WHERE config_key = 'alert_cpu_threshold';
```

### Adicionar novas métricas
1. Edite `collector/src/MetricsCollector.php`
2. Adicione sua função de coleta
3. Atualize o banco de dados
4. Reinicie os serviços

## 🔒 Segurança

⚠️ **Importante**: Este é um ambiente de desenvolvimento. Para produção:

1. Altere as senhas padrão no `docker-compose.yml`
2. Configure HTTPS
3. Implemente autenticação
4. Configure firewall

## 📞 Suporte

- 📖 **Documentação completa**: Veja o `README.md`
- 🐛 **Problemas**: Verifique os logs com `./start.sh logs`
- 💡 **Ideias**: Abra uma issue no GitHub

---

**🎉 Pronto! Seu ambiente IoT está funcionando!**
