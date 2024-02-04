export type GetObjectsFilterCriteria<T> = {
  MAX?: number;
  START?: number;
  VELDEN?: string;
  SORT?: string;
} & T;
