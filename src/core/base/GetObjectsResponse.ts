export interface GetObjectsResponse<Entity> {
  totaal: number;
  laatste_aanpassing: Date;
  dataset: Entity[];
  hash: string;
}
