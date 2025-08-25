# 🚀 IoT Dashboard - Monitoramento de Sistema em Tempo Real

Um sistema completo de monitoramento IoT desenvolvido com Docker, PHP, MySQL e Chart.js para coletar e exibir métricas do sistema em tempo real.

## 📊 Funcionalidades

- **Coleta Automática** de métricas do sistema (CPU, RAM, Disco, Rede)
- **Dashboard Web** responsivo com gráficos interativos
- **Histórico de Dados** com armazenamento em MySQL
- **Atualização em Tempo Real** com auto-reload
- **Interface Moderna** com Bootstrap 5 e Font Awesome
- **Gráficos Dinâmicos** com Chart.js
- **Sistema de Fallback** para carregamento de recursos

## 🛠️ Tecnologias Utilizadas

- **Backend**: PHP 8.1
- **Banco de Dados**: MySQL 8.0
- **Cache/Comunicação**: Redis
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Gráficos**: Chart.js 3.9.1
- **UI Framework**: Bootstrap 5.3.0
- **Containerização**: Docker & Docker Compose
- **Sistema**: Compatível com Linux e macOS

## 🚀 Instalação e Uso

### Pré-requisitos

- Docker
- Docker Compose
- Git

### Passos para Instalação

1. **Clone o repositório:**
```bash
git clone https://github.com/jaderwpdias/Views-processor-docker.git
cd Views-processor-docker
```

2. **Execute o script de inicialização:**
```bash
chmod +x start.sh
./start.sh
```

3. **Acesse o dashboard:**
```
http://localhost:8080
```

### Comandos Disponíveis

```bash
./start.sh start      # Iniciar serviços
./start.sh stop       # Parar serviços
./start.sh restart    # Reiniciar serviços
./start.sh status     # Verificar status
./start.sh logs       # Ver logs
./start.sh test       # Executar testes
```

## 📁 Estrutura do Projeto

```
Views-processor-docker/
├── collector/                 # Serviço coletor de métricas
│   ├── src/
│   │   ├── MetricsCollector.php
│   │   ├── Database.php
│   │   └── collector.php
│   ├── Dockerfile
│   └── composer.json
├── dashboard/                 # Interface web
│   ├── src/
│   │   └── Database.php
│   ├── assets/
│   │   ├── css/
│   │   └── js/
│   │       ├── dashboard.js
│   │       └── lib/
│   ├── api/
│   │   └── metrics.php
│   ├── index.php
│   ├── test-chart.php
│   ├── debug-dashboard.html
│   └── diagnose.php
├── docker-compose.yml         # Configuração Docker
├── start.sh                   # Script de gerenciamento
├── README.md
└── .gitignore
```

## 🔧 Configuração

### Variáveis de Ambiente

O sistema utiliza as seguintes variáveis de ambiente:

- `DB_HOST`: Host do banco de dados (padrão: iot-db)
- `DB_NAME`: Nome do banco (padrão: iot_metrics)
- `DB_USER`: Usuário do banco (padrão: iot_user)
- `DB_PASS`: Senha do banco (padrão: iot_password)
- `HOST_ROOT`: Caminho raiz do host para coleta de métricas (padrão: /host)

### Portas Utilizadas

- **8080**: Dashboard Web
- **3306**: MySQL (externa)
- **6379**: Redis (interna)

## 📈 Métricas Coletadas

### Sistema
- **CPU**: Uso percentual e temperatura
- **Memória**: Uso de RAM e swap
- **Disco**: Uso de espaço e I/O
- **Rede**: Bytes enviados/recebidos
- **Sistema**: Uptime e load average

### Frequência de Coleta
- **Métricas**: A cada 5 segundos
- **Limpeza**: A cada 100 coletas (7 dias de retenção)
- **Atualização UI**: A cada 3 segundos

## 🌐 URLs de Acesso

- **Dashboard Principal**: http://localhost:8080
- **Teste do Gráfico**: http://localhost:8080/test-chart.php
- **Debug Dashboard**: http://localhost:8080/debug-dashboard.html
- **Diagnóstico**: http://localhost:8080/diagnose.php

## 🔍 Troubleshooting

### Problemas Comuns

1. **Chart.js não carrega**
   - Solução: Sistema de fallback automático para CDN

2. **Métricas zeradas**
   - Verificar: Permissões de acesso ao sistema host
   - Solução: Configurar volume Docker corretamente

3. **Erro de conexão com banco**
   - Verificar: Status dos containers Docker
   - Solução: Reiniciar serviços com `./start.sh restart`

### Logs e Debug

```bash
# Ver logs do coletor
docker logs iot-collector

# Ver logs do dashboard
docker logs iot-dashboard

# Executar diagnóstico
php test-final.php
```

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/AmazingFeature`)
3. Commit suas mudanças (`git commit -m 'Add some AmazingFeature'`)
4. Push para a branch (`git push origin feature/AmazingFeature`)
5. Abra um Pull Request

## 📝 Licença

Este projeto está sob a licença MIT. Veja o arquivo `LICENSE` para mais detalhes.

## 👨‍💻 Autor

**Jader Dias**
- GitHub: [@jaderwpdias](https://github.com/jaderwpdias)

## 🙏 Agradecimentos

- [Chart.js](https://www.chartjs.org/) - Biblioteca de gráficos
- [Bootstrap](https://getbootstrap.com/) - Framework CSS
- [Font Awesome](https://fontawesome.com/) - Ícones
- [Docker](https://www.docker.com/) - Containerização

---

⭐ **Se este projeto foi útil, considere dar uma estrela no repositório!**
