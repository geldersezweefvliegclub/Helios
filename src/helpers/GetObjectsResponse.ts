export interface GetObjectsResponse<T> {
  totaal: number;
  laatste_aanpassing: Date;
  dataset: T[];
  hash: string;
}
