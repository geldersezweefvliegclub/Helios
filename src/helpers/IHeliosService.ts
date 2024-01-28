export interface IHeliosService<T> {
    GetObjects(): Promise<T[]>

    CreateViews(): Promise<void>

}