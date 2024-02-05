export type ObjectID<T extends {ID?: number | undefined}> = Pick<T, "ID">
