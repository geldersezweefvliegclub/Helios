import { FindOptionsWhere } from 'typeorm/find-options/FindOptionsWhere';

/**
 * Controleert of de gegeven parameter een object is.
 * Let op, null en een Array hebben ook typeof 'object', maar zijn geen objecten. Deze functie returned false voor deze gevallen.
 * @param obj
 * @example
 * isObject({}) // true
 * @example
 * isObject([]) // false
 * @example
 * isObject(null) // false
 * @example
 * isObject(true) // false
 * @example
 * isObject(undefined) // false
 */
export const isObject = (obj: any) => {
  return obj !== null && typeof obj === 'object' && !Array.isArray(obj);
}


/**
 * Helper functie om vast te stellen of de gegeven parameter van het type FindOptionsWhere of FindOptionsWhere[], een object of een array is.
 * De FindOptionsWhere als object is handig voor het bouwen van AND queries.
 * Met de array kun je OR queries bouwen.
 * @param obj
 */
export const isFindOptionsWhereAnObject = <T>(obj: FindOptionsWhere<T> | FindOptionsWhere<T>[]): obj is FindOptionsWhere<T> => {
  return isObject(obj);
}
