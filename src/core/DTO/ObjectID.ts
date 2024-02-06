/**
 * Returned een object met alleen de ID property van het object.
 * @example
 * type SomeObject = {ID: number, name: string}
 *
 * const idObj: ObjectID<SomeObject> = { ID: 1 }                // valid
 * const idObj: ObjectID<SomeObject> = { ID: 1, name: "John" }  // invalid
 * const idObj: ObjectID<SomeObject> = { name: "John" }         // invalid
 * const idObj: ObjectID<SomeObject> = { ID: "1" }              // invalid
 */
export type ObjectID<T extends {ID?: any}> = Pick<T, "ID">
