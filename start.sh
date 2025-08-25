#!/bin/bash

# IoT Dashboard - Script de Inicialização
# Autor: Sistema IoT
# Versão: 1.0

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Função para imprimir mensagens coloridas
print_message() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}================================${NC}"
    echo -e "${BLUE}    IoT Dashboard - Sistema     ${NC}"
    echo -e "${BLUE}================================${NC}"
}

# Verificar se Docker está instalado
check_docker() {
    if ! command -v docker &> /dev/null; then
        print_error "Docker não está instalado. Por favor, instale o Docker primeiro."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose não está instalado. Por favor, instale o Docker Compose primeiro."
        exit 1
    fi
    
    print_message "Docker e Docker Compose encontrados."
}

# Verificar se as portas estão livres
check_ports() {
    local ports=("8080" "3306" "6379")
    
    for port in "${ports[@]}"; do
        if lsof -Pi :$port -sTCP:LISTEN -t >/dev/null 2>&1; then
            print_warning "Porta $port já está em uso. Verifique se não há outro serviço rodando."
        fi
    done
}

# Iniciar serviços
start_services() {
    print_message "Iniciando serviços IoT..."
    
    # Parar serviços existentes se houver
    docker-compose down 2>/dev/null || true
    
    # Construir e iniciar
    docker-compose up -d --build
    
    print_message "Serviços iniciados com sucesso!"
}

# Verificar status dos serviços
check_status() {
    print_message "Verificando status dos serviços..."
    
    # Aguardar um pouco para os serviços inicializarem
    sleep 10
    
    local services=("iot-db" "iot-redis" "iot-collector" "iot-dashboard")
    local all_running=true
    
    for service in "${services[@]}"; do
        if docker-compose ps | grep -q "$service.*Up"; then
            print_message "✓ $service está rodando"
        else
            print_error "✗ $service não está rodando"
            all_running=false
        fi
    done
    
    if [ "$all_running" = true ]; then
        print_message "Todos os serviços estão funcionando corretamente!"
    else
        print_warning "Alguns serviços podem não estar funcionando. Verifique os logs:"
        echo "  docker-compose logs"
    fi
}

# Mostrar informações de acesso
show_access_info() {
    echo ""
    print_message "🎉 IoT Dashboard está pronto!"
    echo ""
    echo -e "${BLUE}📊 Dashboard Web:${NC}"
    echo "   URL: http://localhost:8080"
    echo ""
    echo -e "${BLUE}🗄️  Banco de Dados:${NC}"
    echo "   Host: localhost"
    echo "   Porta: 3306"
    echo "   Database: iot_metrics"
    echo "   Usuário: iot_user"
    echo "   Senha: iot_password"
    echo ""
    echo -e "${BLUE}📡 API Endpoints:${NC}"
    echo "   Métricas: http://localhost:8080/api/metrics.php?action=latest"
    echo "   Histórico: http://localhost:8080/api/metrics.php?action=history"
    echo ""
    echo -e "${BLUE}🔧 Comandos Úteis:${NC}"
    echo "   Ver logs: docker-compose logs -f"
    echo "   Parar: docker-compose down"
    echo "   Reiniciar: docker-compose restart"
    echo ""
}

# Função principal
main() {
    print_header
    
    print_message "Verificando pré-requisitos..."
    check_docker
    check_ports
    
    print_message "Iniciando ambiente IoT..."
    start_services
    
    check_status
    show_access_info
    
    # Executar teste do sistema
    print_message "Executando teste do sistema..."
    if docker-compose exec iot-dashboard php /var/www/html/test-system.php 2>/dev/null; then
        print_message "Teste do sistema concluído!"
    else
        print_warning "Teste do sistema não pôde ser executado. Verifique os logs."
    fi
    
    print_message "Sistema IoT iniciado com sucesso! 🚀"
}

# Verificar argumentos da linha de comando
case "${1:-}" in
    "stop")
        print_message "Parando serviços IoT..."
        docker-compose down
        print_message "Serviços parados."
        ;;
    "restart")
        print_message "Reiniciando serviços IoT..."
        docker-compose restart
        print_message "Serviços reiniciados."
        ;;
    "logs")
        print_message "Mostrando logs dos serviços..."
        docker-compose logs -f
        ;;
    "status")
        check_status
        ;;
    "test")
        print_message "Executando teste do sistema..."
        docker-compose exec iot-dashboard php /var/www/html/test-system.php
        ;;
    "test-metrics")
        print_message "Executando teste de métricas..."
        docker-compose exec iot-collector php /app/test-metrics.php
        ;;
    "help"|"-h"|"--help")
        echo "Uso: $0 [comando]"
        echo ""
        echo "Comandos:"
        echo "  start   - Iniciar serviços (padrão)"
        echo "  stop    - Parar serviços"
        echo "  restart - Reiniciar serviços"
        echo "  logs    - Mostrar logs"
        echo "  status  - Verificar status"
        echo "  test    - Executar teste do sistema"
        echo "  test-metrics - Testar coleta de métricas"
        echo "  help    - Mostrar esta ajuda"
        ;;
    *)
        main
        ;;
esac
