export interface GetObjectsResponse<T> {
  totaal: number;
  laatsteAanpassing: Date;
  dataset: T[];
  hash: string;
}
