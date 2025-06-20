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

## 🗂️ Estrutura de Diretórios

```
app/
├── control/
│   └── Api/
│       ├── PokemonApiController.php
│       ├── PokeApiProxyController.php
│       └── TipoApiController.php
├── service/
│   ├── PokemonService.php
│   ├── TipoService.php
│   └── PokeApiClient.php
├── model/
│   ├── Pokemon.php
│   └── Tipo.php
├── database/
│   └── pokemon.db
├── cache/
│   └── tipos_ptbr.json
├── routes/
│   └── api_routes.php (opcional)
```

---

## 📌 Considerações sobre boas práticas

- **Endpoints personalizados** garantem autonomia sobre o formato dos dados e regras de negócio.
- Evitar dependência direta da PokéAPI em tempo real melhora a **resiliência** e **velocidade**.
- Utilizar **cache** para evitar múltiplas requisições repetidas.
- Separação clara entre **controladores**, **serviços**, e **modelos** facilita testes e manutenção.

---

## 📚 Tecnologias

- PHP com Adianti Framework
- PokéAPI (https://pokeapi.co/)
- SQLite (durante desenvolvimento) ou outro banco relacional
- Formato JSON nas respostas da API

---

## 🚀 Próxima Etapa

Implementar o primeiro endpoint:
- `GET /api/pokemons` → retorna os pokémons cadastrados no banco de dados.
