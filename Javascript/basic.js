// Variables
let name = "John";
const age = 25;
var city = "New York";

// Data Types
let number = 42;
let string = "Hello";
let boolean = true;
let array = [1, 2, 3, 4];
let object = { name: "Alice", age: 30 };

// Functions
function greet(person) {
    return `Hello, ${person}!`;
}

// Arrow Function
const add = (a, b) => a + b;

// Conditionals
if (age >= 18) {
    console.log("Adult");
} else {
    console.log("Minor");
}

// Loops
for (let i = 0; i < 5; i++) {
    console.log(i);
}

// Array Methods
array.forEach(item => console.log(item));

// Console Output
console.log(greet(name));
console.log(add(5, 3));