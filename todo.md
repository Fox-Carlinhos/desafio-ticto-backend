Desafio Ticto Backend
Campinas, 20 de agosto de 2025.
Olá,
Você foi aprovado para a próxima fase: “Luz, Câmera & Ação!” do processo seletivo de:
Desenvolvedor Back-end Sênior.
Objetivo
Este desafio tem como propósito avaliar sua capacidade técnica, raciocínio lógico, organização de código, domínio do ecossistema Laravel e criatividade na resolução de problemas comuns do dia a dia de desenvolvimento backend.
Tecnologias obrigatórias
O projeto deve ser desenvolvido utilizando:

PHP com Laravel (última versão estável): https://laravel.com/
MySQL (última versão estável)
Engine: InnoDB
Índices e relacionamentos devem ser criados corretamente.

Requisitos técnicos:

O uso de Migrations do Laravel é obrigatório.
A implementação de um front-end simples é recomendada, mas não obrigatória.
Você pode utilizar templates prontos, painéis administrativos existentes ou componentes simples em Blade ou outra stack compatível.

Escopo funcional
O sistema a ser desenvolvido é uma aplicação simples de registro de ponto eletrônico para funcionários via navegador.
Níveis de acesso:

Funcionário

Subordinado a um Gestor (Administrador)

Administrador

Responsável pelo cadastro e gestão dos funcionários

Funcionalidades por perfil:
Funcionário:

Login com autenticação.
Registro de ponto (apenas um botão).
Troca de senha.

Administrador:

CRUD completo de funcionários (Listar, Criar, Editar, Remover).
Acesso à lista de pontos registrados por qualquer funcionário (não apenas os subordinados).
Filtragem por período (entre duas datas).
Cada funcionário deve estar associado ao Administrador que o cadastrou.

Dados obrigatórios dos usuários:

Nome completo
CPF

Validação obrigatória (deve recusar CPFs inválidos ou duplicados)

E-mail
Senha
Cargo
Data de nascimento
CEP

Deve realizar consulta automática (ex.: API dos Correios ou viaCep)

Endereço completo (preenchido automaticamente após a consulta do CEP)

Banco de Dados

Toda manipulação de dados (exceto o relatório abaixo) deve ser feita usando Eloquent.
Relacionamentos devem estar corretamente definidos via Migrations e Models.

Requisito especial de relatório:
Deve ser implementada uma listagem de registros de ponto contendo:

ID do Registro
Nome do Funcionário
Cargo
Idade
Nome do Gestor
Data e Hora Completa do Registro (com segundos)

➢ Esta listagem deve ser construída usando SQL puro (sem Eloquent).
Será avaliada a escrita do SELECT, joins, filtragens por datas e uso correto dos relacionamentos.
Entrega

O projeto deve ser disponibilizado em um repositório público ou privado no GitHub.
Serão avaliados:

Clareza e estrutura dos commits
Frequência das entregas parciais
Boas práticas de versionamento

Organização do código

Dica: Use commits descritivos e pequenos ciclos de entrega. Isso será considerado um diferencial.
Critérios de Avaliação

Aderência aos requisitos
Qualidade e legibilidade do código
Organização de estrutura e pastas
Boas práticas de Laravel
Uso correto de Eloquent, validações e Migrations
Clareza no SQL puro solicitado
Uso consciente de ferramentas e bibliotecas
Entrega com histórico de versionamento coerente

Caso tenha dúvidas pontuais durante a realização, registre no README ou adicione comentários no código.
Prazo de entrega: Você possui até 25/08/2025 às 9h para entrega do teste e deverá ser encaminhado para rh@ticto.com.br.
Boa sorte!
