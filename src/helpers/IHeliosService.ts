import { GetObjectsResponse } from './GetObjectsResponse';

export interface IHeliosService<T> {
    GetObjects(): Promise<GetObjectsResponse<T>>

    CreateViews(): Promise<void>

}
