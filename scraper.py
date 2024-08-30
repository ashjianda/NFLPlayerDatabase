import nfl_data_py as nfl
import pandas as pd

def import_seasonal_data_to_excel(years, filename):
    data = nfl.import_seasonal_data(years, 'ALL')
    
    df = pd.DataFrame(data)
    df.drop(['sacks', 'sack_yards', 'passing_air_yards', 'passing_yards_after_catch', 'passing_first_downs', 
             'passing_epa', 'passing_2pt_conversions', 'pacr', 'dakota', 'rushing_first_downs' , 'rushing_epa', 
             'rushing_2pt_conversions', 'receiving_air_yards', 'receiving_first_downs', 'receiving_epa', 
             'receiving_2pt_conversions', 'racr', 'target_share', 'air_yards_share', 'wopr_x', 'fantasy_points', 
             'fantasy_points_ppr', 'tgt_sh', 'ay_sh','yac_sh', 'wopr_y','ry_sh','rtd_sh','rfd_sh','rtdfd_sh','dom',
             'w8dom','yptmpa','ppr_sh'], inplace=True, axis=1)
    df.to_excel(filename, index=False)

def ids():
    id = nfl.import_ids()
    df = pd.DataFrame(id)
    # df.drop(['mfl_id', 'sportradar_id', 'fantasypros_id', 'sleeper_id', 'nfl_id', 'espn_id', 'yahoo_id', 'pff_id', 
    #          'fleaflicker_id', 'cbs_id', 'cfbref_id', 'rotowire_id', 'rotoworld_id', 'ktc_id', 'stats_id', 
    #          'stats_global_id', 'fantasy_data_id', 'swish_id', 'twitter_username', 'db_season'], inplace=True, axis=1)
    df.to_excel("id.xlsx", index=False)

def merge_sheet(sheet1, sheet2):
    df1 = pd.read_csv(sheet1)
    df2 = pd.read_csv(sheet2)
    df = pd.merge(df1, df2, on=['player_id'], how='left')
    df.to_csv("player.csv", index=False)

def roster(years):
    data = nfl.import_seasonal_rosters(years)
    df = pd.DataFrame(data)
    df.to_csv("nfl_roster_data.csv", index=False)

def clean_offense(final):
    data = pd.read_csv(final)
    df = pd.DataFrame(data)
    df = df[['player_id', 'season', 'games', 'carries', 'rushing_yards', 'rushing_tds', 'sack_fumbles_lost', 
             'rushing_fumbles_lost', 'receiving_fumbles_lost', 'targets', 'receptions','receiving_yards', 
             'receiving_tds', 'receiving_yards_after_catch', 'position']]
    df = df.groupby(['player_id', 'season']).apply(merge_offense_rows).reset_index(drop=True)
    df.to_csv("offense.csv", index=False)

def merge_offense_rows(group):
    merged_row = {}
    for column_name, values in group.items():
        if column_name in ['player_id', 'position', 'season', 'games']:
            merged_row[column_name] = values.iloc[0]
        elif column_name in ['rushing_yards', 'sack_fumbles_lost', 'rushing_fumbles_lost', 'receiving_fumbles_lost',
                             'receiving_yards', 'carries', 'receptions', 'targets', 'receiving_yards_after_catch',
                             'rushing_tds', 'receiving_tds']:
            merged_row[column_name] = values.sum()
    return pd.Series(merged_row)

def merge_qb_rows(group):
    merged_row = {}
    for column_name, values in group.items():
        if column_name in ['player_id', 'season']:
            merged_row[column_name] = values.iloc[0]
        elif column_name in ['completions','attempts','passing_yards','passing_tds','interceptions']:
            merged_row[column_name] = values.sum()
    return pd.Series(merged_row)

def load_player(final):
    data = pd.read_excel(final)
    df = pd.DataFrame(data)
    df = df[['player_id', 'player_name', 'weight_x', 'height_x', 'birthdate', 'position_x', 'draft_number', 'entry_year', 'college_x']]
    df = df.groupby(['player_id']).first().reset_index()
    df.to_csv("player.csv", index=False)

def load_qb(final):
    data = pd.read_excel(final)
    df = pd.DataFrame(data)
    df = df[df['position_x'] == 'QB']
    df = df[['player_id','season','completions','attempts','passing_yards','passing_tds','interceptions']]
    df = df.groupby(['player_id', 'season']).apply(merge_qb_rows).reset_index(drop=True)
    df.to_csv("qb.csv", index=False)

def clean_spec_team(final_spec):
    data = pd.read_csv(final_spec)
    df = pd.DataFrame(data)
    df = df[['player_id', 'season', 'fg_att', 'fg_made', 'fg_blocked', 'fg_long', 'fg_made_0_19', 'fg_made_20_29', 
             'fg_made_30_39', 'fg_made_40_49', 'fg_made_50_59', 'fg_made_60_', 'fg_pct', 'pat_blocked', 'pat_att', 
             'pat_made', 'pat_pct']]
    df = df.groupby(['player_id', 'season']).apply(merge_spec_team).reset_index(drop=True)
    df.to_csv("spec_team_kickers.csv", index=False)

def merge_spec_team(group):
    merged_row = {}
    for column_name, values in group.items():
        if column_name in ['player_id', 'season']:
            merged_row[column_name] = values.iloc[0]
        elif column_name in ['fg_att', 'fg_made', 'fg_blocked', 'fg_made_0_19', 'fg_made_20_29', 'fg_made_30_39', 
                             'fg_made_40_49', 'fg_made_50_59', 'fg_made_60_', 'pat_blocked', 'pat_att', 'pat_made']:
            merged_row[column_name] = values.sum()
        elif column_name in ['fg_pct', 'pat_pct']:
            merged_row[column_name] = round(values.mean(), 2)
        elif column_name in ['fg_long']:
            merged_row[column_name] = values.max()
    return pd.Series(merged_row)

def clean_def(defs):
    data = pd.read_csv(defs)
    df = pd.DataFrame(data)
    df = df[['player_id', 'season', 'games', 'def_tackles', 'def_tackles_solo', 'def_tackle_assists', 
             'def_tackles_for_loss', 'def_fumbles_forced', 'def_fumbles', 'def_sacks', 'def_interceptions', 
             'def_pass_defended', 'def_tds', 'def_safety' , 'position']]
    df = df.groupby(['player_id', 'season']).apply(merge_def).reset_index(drop=True)
    df.to_csv("defense.csv", index=False)

def merge_def(group):
    merged_row = {}
    for column_name, values in group.items():
        if column_name in ['player_id', 'season', 'games', 'position']:
            merged_row[column_name] = values.iloc[0]
        else:
            merged_row[column_name] = values.sum()
    return pd.Series(merged_row)

def load_roster(rost): 
    data = pd.read_excel(rost)
    df = pd.DataFrame(data)
    df = df[['player_id', 'team', 'season', 'jersey_number']]
    df.to_csv("plays.csv", index=False)

def clean_punting(punt, years):
    data = pd.read_csv(punt)
    df = pd.DataFrame(data)
    df = df[df['Year'].isin(years)]
    df = df[['Player Id', 'Year', 'Games Played', 'Punts', 'Longest Punt', 'Gross Punting Yards', 'Gross Punting Average', 'Punts Blocked']]
    df.to_csv("punting.csv", index=False)

def punt_ids():
    data = pd.read_excel("id.xlsx")
    df = pd.DataFrame(data)
    df = df[['gsis_id', 'nfl_id']]
    df = df.dropna()
    df.to_csv("punt_ids.csv", index=False)

def drop_punt_rows():
    data = pd.read_csv("final_punting.csv")
    df = pd.DataFrame(data)
    df = df.dropna()
    df.to_csv("final_punting.csv", index=False)

def clean_punt_return(years):
    data = pd.read_csv("punt_return.csv")
    df = pd.DataFrame(data)
    df = df[df['Year'].isin(years)]
    df = df[['Player Id', 'Year', 'Games Played', 'Returns', 'Yards Returned', 'Yards Per Return', 'Longest Return', 'Returns for TDs', 'Fumbles']]
    df = df.dropna()
    df.to_csv("punt_return.csv", index=False)

def drop_punt_return_rows():
    data = pd.read_csv("final_punt_return.csv")
    df = pd.DataFrame(data)
    df = df.dropna()
    df.to_csv("final_punt_return.csv", index=False)

def clean_kick_return(years):
    data = pd.read_csv("Career_Stats_Kick_Return.csv")
    df = pd.DataFrame(data)
    df = df[df['Year'].isin(years)]
    df = df[['Player Id', 'Year', 'Games Played', 'Returns', 'Yards Returned', 'Yards Per Return', 'Longest Return', 'Returns for TDs', 'Fumbles']]
    df = df.dropna()
    df.to_csv("kick_return.csv", index=False)

def drop_kick_return_rows():
    data = pd.read_csv("final_kick_return.csv")
    df = pd.DataFrame(data)
    df = df.dropna()
    df.to_csv("final_kick_return.csv", index=False)

def merge_headshots(headshots):
    data = pd.read_csv(headshots)
    df = pd.DataFrame(data)
    df = df.groupby(['player_id']).first().reset_index()
    df.to_csv("headshots.csv", index=False)


years = [1999, 2000, 2001, 2002, 2003, 2004, 2005, 2006, 2007, 2008, 2009, 2010, 2011, 2012, 2013, 2014, 2015, 2016, 2017, 2018, 
          2019, 2020, 2021, 2022, 2023]
# stats = "nfl_seasonal_data.xlsx"
# id = "nfl_id_data.xlsx"
# rost = "nfl_roster_data.xlsx"
# merged = "merged_roster.xlsx"
# final = "final_roster.xlsx"
# final_spec = "player_stats_kicking.csv"
# defs = "player_stats_def.csv"
# off = "player_stats.csv"
merge_sheet("player.csv","headshots.csv")