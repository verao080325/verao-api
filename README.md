# API de Licenciamento - Verão

Esta API gera e valida licenças para o software **Verão**.

## Endpoints

- **POST /licenca.php**: Gera uma nova licença.
- **POST /validar.php**: Valida uma licença existente.

## Estrutura

- `Public/`: Arquivos acessíveis pela web.
- `App/`: Lógica de negócios e classes.
- `Keys/`: Contém as chaves públicas e privadas.
- `Logs/`: Logs da API.
