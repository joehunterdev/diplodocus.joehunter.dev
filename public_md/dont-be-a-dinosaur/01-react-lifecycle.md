# React Component Lifecycle

![React lifecycle hero](attachments/01a-hero.png)

**Dinosaurs don't understand growing up.**

## Rex's mistake

Rex the T-Rex thinks his React component only runs once. He puts a `console.log('COMPONENT LOADED!')` at the top of his function and is **shocked** when it logs 47 times in a row.

He blames React. He blames his computer. He blames the meteor that killed the dinosaurs.

But the real problem? **Rex doesn't understand what a component IS.**

Don't be like Rex.

---

## The egg model: What IS a component?

A React component is a **function that returns JSX**. That's it. It's not a template. It's not a class instance (unless you're using legacy code). It's just a function.

```jsx
function Diplo() {
  console.log('I hatched!');
  const [age, setAge] = useState(0);
  
  return <div>I am {age} years old</div>;
}
```

Think of your component as **Diplo's egg**:

![The egg model](attachments/01b-egg-model.png)

- The **egg** = your component function (contains all the code)
- The **food inside** = your state and props (what makes Diplo grow)
- **Hatching** = React executing your function
- The **baby dino** = the JSX that gets returned

Here's the part Rex doesn't understand: **The egg hatches EVERY time something changes.**

---

## What triggers a re-render?

Three things make Diplo's egg hatch again:

### 1. State changes (meteor strike)

When you call `setState()`, React says *"Time to hatch again!"* and re-runs your entire component function.

```jsx
function Diplo() {
  const [age, setAge] = useState(0);
  
  // This causes a RE-RENDER (egg hatches again)
  const growUp = () => setAge(age + 1);
  
  return <button onClick={growUp}>Birthday</button>;
}
```

![Meteor triggers re-render](attachments/01c-meteor-trigger.png)

### 2. Props change (parent pokes the egg)

When your parent component passes NEW props, your egg hatches again with the new data.

```jsx
function BabyDino({ name }) {
  console.log('Hatched with name:', name);
  return <div>Hello, I'm {name}</div>;
}

// If parent changes name from "Diplo" to "Rex",
// BabyDino's egg hatches again
```

![Props change trigger](attachments/01d-props-trigger.png)

### 3. Force update (Pterry shakes the egg)

You can manually force a re-render with `forceUpdate()` (class components) or a state trick (function components). But this is rare—usually you have a bug if you need this.

---

## The render phase: Drawing the blueprint

When Diplo's egg hatches, here's what happens:

1. **React calls your function** — The entire function runs from top to bottom
2. **All your code executes** — Hooks run, variables get created, logic happens
3. **JSX becomes instructions** — Your `<div>Hello</div>` becomes `React.createElement('div', null, 'Hello')`
4. **React gets a blueprint** — A JavaScript object describing what the UI should look like

![Render phase diagram](attachments/01e-render-phase.png)

🦖 **REX'S PITFALL:** Rex thinks JSX IS the DOM. It's not! JSX is just a blueprint. React uses it to figure out what to change in the real DOM.

Here's what your JSX becomes:

```jsx
// You write this:
<div className="dino">
  <h1>Diplo</h1>
</div>

// React turns it into this:
{
  type: 'div',
  props: {
    className: 'dino',
    children: {
      type: 'h1',
      props: { children: 'Diplo' }
    }
  }
}
```

This object is called a **React element**. It's lightweight, fast to create, and easy to compare.

---

## Reconciliation: Comparing blueprints

Now React has TWO blueprints:

- **Old blueprint** (from the previous render)
- **New blueprint** (from the render that just happened)

React compares them side-by-side like an archaeologist comparing fossils:

![Reconciliation diagram](attachments/01f-reconciliation.png)

If the blueprints are identical, React does **nothing**. No DOM updates. Your component hatched, created a blueprint, but nothing changed on screen.

If they're different, React marks **only the differences** for updating.

```jsx
// Render 1:
<div>Age: 0</div>

// Render 2 (after setAge(1)):
<div>Age: 1</div>

// React sees: "Only the TEXT changed. I'll update just that."
```

🦕 **DIPLO SAYS:** This is why React is fast! It doesn't rebuild the whole DOM—just the parts that changed.

---

## The commit phase: Building the real thing

Now React takes the **list of changes** and applies them to the real DOM.

This is called the **commit phase**:

![Commit phase diagram](attachments/01g-commit-phase.png)

Only during the commit phase does:
- The **real DOM** update (you can see changes on screen)
- **useEffect** hooks run (because they need the DOM to be ready)
- **useState** updates get "committed" (more on this in a moment)

🦖 **REX'S PITFALL:** Rex calls `setState(5)` and immediately tries to read the state. He gets the OLD value and screams "React is broken!"

```jsx
const [count, setCount] = useState(0);

function broken() {
  setCount(5);
  console.log(count); // Still 0! ❌
  // State hasn't updated yet—commit phase comes AFTER this function finishes
}
```

The fix? Use the NEW value in the NEXT render, or use `useEffect` to react to changes:

```jsx
function fixed() {
  setCount(5);
  // State will be 5 in the NEXT render
}

// Or:
useEffect(() => {
  console.log('Count changed to:', count);
}, [count]);
```

---

## The full lifecycle in one diagram

Here's the complete flow, based on the diagram you showed:

![Full React lifecycle](attachments/01h-full-lifecycle.png)

```
TRIGGER (setState, props change)
    ↓
RENDER PHASE
    ├─ Component function runs
    ├─ JSX → React.createElement()
    ├─ New element tree created
    └─ Compare with previous render
         ↓
COMMIT PHASE
    ├─ Update only changed DOM nodes
    ├─ Run useEffect hooks
    └─ Commit useState updates
         ↓
SCREEN UPDATES (you see the change)
```

---

## Common tar pits

Even smart dinosaurs fall into these traps:

### Tar Pit 1: "My state isn't updating!"

```jsx
const [count, setCount] = useState(0);

function wrong() {
  setCount(count + 1);
  setCount(count + 1); // Still adds just 1! ❌
  // Both calls use the SAME old value of count
}

function right() {
  setCount(prev => prev + 1);
  setCount(prev => prev + 1); // Adds 2 ✅
  // Each call gets the UPDATED value
}
```

### Tar Pit 2: "Everything re-renders a million times!"

```jsx
function expensive() {
  const [data, setData] = useState(null);
  
  // ❌ This runs on EVERY render, causing infinite loop!
  fetch('/api').then(res => setData(res));
  
  return <div>{data}</div>;
}

function smart() {
  const [data, setData] = useState(null);
  
  // ✅ This runs ONCE (on mount)
  useEffect(() => {
    fetch('/api').then(res => setData(res));
  }, []); // Empty array = run once
  
  return <div>{data}</div>;
}
```

### Tar Pit 3: "My console.log fired 100 times!"

**That's normal.** If your component re-renders 100 times, the function runs 100 times. That's how React works.

If you don't WANT 100 re-renders, figure out what's triggering them (usually a state update in the wrong place).

---

## Try it yourself

Can you spot the bug in Rex's code?

```jsx
function RexCounter() {
  const [count, setCount] = useState(0);
  
  setCount(count + 1); // 🦖 Rex did this!
  
  return <div>Count: {count}</div>;
}
```

<details>
<summary>Show answer</summary>

**Bug:** `setCount()` is called DURING the render, which triggers ANOTHER render, which calls `setCount()` again—**infinite loop!**

**Fix:** Only call `setCount()` in response to an event or in `useEffect`:

```jsx
function DiploCounter() {
  const [count, setCount] = useState(0);
  
  return (
    <div>
      Count: {count}
      <button onClick={() => setCount(count + 1)}>+1</button>
    </div>
  );
}
```

</details>

---

## Recap

> **A React component is a function that returns JSX. Every time state or props change, the ENTIRE function runs again. React compares the old and new JSX, then updates only what changed in the DOM.**

If you remember nothing else, remember this: **The egg hatches every time.**

🦕 **DINO RATING:** ⭐⭐⭐⭐⭐ — You've evolved! You understand the render cycle now.

---

🦴 **FOSSIL FACT:** Before React, dinosaurs used jQuery to manually update every single DOM element. Many perished in tar pits of spaghetti code.

**Next:** [JavaScript Event Loop →](event-loop)
