# 🧩 Projeto de API Pokémon com Adianti Framework

Este projeto tem como objetivo praticar conceitos de estruturação de APIs RESTful utilizando o **Adianti Framework** em PHP, consumindo dados da **PokéAPI**, permitindo armazenamento local e adição de Pokémons personalizados.

---

## ✅ Etapa 1: Planejamento

### 🎯 Objetivo da API

- Consumir dados da PokéAPI (Nome, Tipo, Descrição, Imagem).
- Armazenar esses dados no banco local para performance e personalização.
- Permitir que o usuário crie e edite Pokémons próprios.
- A API deverá oferecer **modo de atualização** ou **apenas carregamento** dos dados da PokéAPI (através de parâmetro).

---

## 🔁 Estratégia de Integração

- Dados da PokéAPI **serão consumidos e salvos no banco local**.
- A API **não fará requisições diretas para exibição comum**, a não ser por endpoints “proxy” criados para esse fim.
- Haverá uma separação entre:
  - Pokémons **oficiais (originais da PokéAPI)**.
  - Pokémons **personalizados (criados pelo usuário)**.

---

## 🔗 Endpoints da API

| Método | Rota | Tipo | Descrição |
|--------|------|------|-----------|
| `GET` | `/api/pokemons` | Personalizado | Lista todos os Pokémons (originais + personalizados) |
| `GET` | `/api/pokemons/{id}` | Personalizado | Detalhes de um Pokémon |
| `POST` | `/api/pokemons` | Personalizado | Cadastra novo Pokémon |
| `PUT` | `/api/pokemons/{id}` | Personalizado | Atualiza um Pokémon criado |
| `DELETE` | `/api/pokemons/{id}` | Personalizado | Remove um Pokémon personalizado |
| `GET` | `/api/tipos` | Personalizado | Lista os tipos em pt-BR |
| `GET` | `/api/sincronizar?modo=carregar|atualizar` | Personalizado | Importa dados da PokéAPI |
| `GET` | `/api/pokeapi/pokemon/{nome}` | Proxy (original) | Consulta direta à PokéAPI por nome |
| `GET` | `/api/pokeapi/tipo/{id}` | Proxy (original) | Consulta direta ao tipo na PokéAPI |
| `GET` | `/api/pokeapi/lista` | Proxy (original) | Lista os Pokémons diretamente da PokéAPI |

---

## 📚 Tecnologias

- PHP com Adianti Framework
- PokéAPI (https://pokeapi.co/)
- PostgreSQL
- Formato JSON nas respostas da API

---

## 📷 Imagens

<img width="1373" height="814" alt="image" src="https://github.com/user-attachments/assets/311e37ab-a48c-467e-babc-730e27e36c0e" />

