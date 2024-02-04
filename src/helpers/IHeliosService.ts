import { GetObjectsResponse } from './GetObjectsResponse';
import { GetObjectsFilterCriteria } from './FilterCriteria';

export interface IHeliosService<T> {
  GetObjects(filter: GetObjectsFilterCriteria<T>): Promise<GetObjectsResponse<T>>;
}
