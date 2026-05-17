---
trigger: always_on
---

# GEMINI.md - Antigravity Kit

> This file defines how the AI behaves in this workspace.

---

## CRITICAL: AGENT & SKILL PROTOCOL (START HERE)

> **MANDATORY:** You MUST read the appropriate agent file and its skills BEFORE performing any implementation. This is the highest priority rule.

#### 1. Modular Skill Loading Protocol

Agent activated → Check frontmatter "skills:" → Read SKILL.md (INDEX) → Read specific sections.

- **Selective Reading:** DO NOT read ALL files in a skill folder. Read `SKILL.md` first, then only read sections matching the user's request.
- **Rule Priority:** P0 (GEMINI.md) > P1 (Agent .md) > P2 (SKILL.md). All rules are binding.

#### 2. Enforcement Protocol

When agent is activated:

- ✅ **Activate:** Read Rules → Check Frontmatter → Load SKILL.md → Apply All
- **Forbidden:** Never skip reading agent rules or skill instructions. "Read → Understand → Apply" is mandatory

---

## 📥 REQUEST CLASSIFIER (STEP 1)

Before ANY action, classify the request:

| Request Type     | Trigger Keywords                           | Active Tiers                   | Result                      |
| ---------------- | ------------------------------------------ | ------------------------------ | --------------------------- |
| **QUESTION**     | "what is", "how does", "explain"           | TIER 0 only                    | Text Response               |
| **SURVEY/INTEL** | "analyze", "list files", "overview"        | TIER 0 + Explorer              | Session Intel (No File)     |
| **SIMPLE CODE**  | "fix", "add", "change" (single file)       | TIER 0 + TIER 1 (lite)         | Inline Edit                 |
| **COMPLEX CODE** | "build", "create", "implement", "refactor" | TIER 0 + TIER 1 (full) + Agent | **{task-slug}.md Required** |
| **DESIGN/UI**    | "design", "UI", "page", "dashboard"        | TIER 0 + TIER 1 + Agent        | **{task-slug}.md Required** |
| **SLASH CMD**    | /create, /orchestrate, /debug              | Command-specific flow          | Variable                    |

---

## 🤖 INTELLIGENT AGENT ROUTING (STEP 2 - AUTO)

**ALWAYS ACTIVE:** Before responding to ANY request, automatically analyze and select the best agent(s).

> 🔴 **MANDATORY:** You MUST follow the protocol defined in `@[skills/intelligent-routing]`.

#### Auto-Selection Protocol

1. **Analyze (Silent):** Detect domains (Frontend, Backend, Security, etc.) from user request
2. **Select Agent(s):** Choose the most appropriate specialist(s)
3. **Inform User:** Concisely state which expertise is being applied
4. **Apply:** Generate response using the selected agent's persona and rules

#### Response Format (MANDATORY)

When auto-applying an agent, inform the user:

```markdown
**Applying knowledge of `@[agent-name]`...**

[Continue with specialized response]
```

**Rules:**

- **Silent Analysis:** No verbose meta-commentary ("I am analyzing...")
- **Respect Overrides:** If user mentions @agent, use it
- **Complex Tasks:** For multi-domain requests, use orchestrator and ask Socratic questions first

### ⚠️ AGENT ROUTING CHECKLIST (MANDATORY BEFORE EVERY CODE/DESIGN RESPONSE)

**Before ANY code or design work, you MUST complete this mental checklist:**

| Step | Check                                                  | If Unchecked                                |
| ---- | ------------------------------------------------------ | ------------------------------------------- |
| 1    | Did I identify the correct agent for this domain?      | → STOP. Analyze request domain first        |
| 2    | Did I READ the agent's .md file (or recall its rules)? | → STOP. Open .agent/agents/{agent}.md       |
| 3    | Did I announce `Applying knowledge of @[agent]...`?    | → STOP. Add announcement before response    |
| 4    | Did I load required skills from agent's frontmatter?   | → STOP. Check `skills:` field and read them |

**Failure Conditions:**

- ❌ Writing code without identifying an agent = **PROTOCOL VIOLATION**
- ❌ Skipping the announcement = **USER CANNOT VERIFY AGENT WAS USED**
- ❌ Ignoring agent-specific rules = **QUALITY FAILURE**

> 🔴 **Self-Check Trigger:** Every time you are about to write code or create UI, ask yourself:
> "Have I completed the Agent Routing Checklist?" If NO → Complete it first.

---

## TIER 0: UNIVERSAL RULES (Always Active)

### 🌐 Language Handling

- When user's prompt is NOT in English: Internally translate for better comprehension
- **Respond in user's language** - match their communication
- Code comments/variables remain in English

### 🧹 Clean Code (Global Mandatory)

**ALL code MUST follow `@[skills/clean-code]` rules. No exceptions.**

- **Code:** Concise, direct, no over-engineering. Self-documenting
- **Testing:** Mandatory. Custom test framework for PHP, Vitest for Vue
- **Performance:** Measure first. Adhere to 2025 standards
- **Infra/Safety:** Docker + Coolify deployment. Verify secrets security

### 📁 File Dependency Awareness

Before modifying ANY file:

1. Check existing similar implementations in codebase
2. Identify dependent files
3. Update ALL affected files together

### 🗺️ System Map Read

🔴 **MANDATORY:** Read `ARCHITECTURE.md` at session start to understand Agents, Skills, and Scripts.

**Path Awareness:**

- **Agents:** `.agent/agents/` (Project)
- **Skills:** `.agent/skills/` (Project)
- **Runtime Scripts:** `.agent/skills/<skill>/scripts/`

---
