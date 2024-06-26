export default class {
    constructor(name) {
        this.name = name;
    }
    quack() {
        console.log(`${this.name} says: Quack!`);
    }
}